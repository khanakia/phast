<?php

namespace Kibo\Phast\Filters\HTML\ImagesOptimizationService\Tags;

use Kibo\Phast\Filters\HTML\HTMLFilter;
use Kibo\Phast\Logging\LoggingTrait;
use Kibo\Phast\Retrievers\Retriever;
use Kibo\Phast\Security\ServiceSignature;
use Kibo\Phast\Services\ServiceRequest;
use Kibo\Phast\ValueObjects\URL;

class Filter implements HTMLFilter {
    use LoggingTrait;

    /**
     * @var ServiceSignature
     */
    protected $signature;

    /**
     * @var Retriever
     */
    protected $retriever;

    /**
     * @var URL
     */
    protected $baseUrl;

    /**
     * @var URL
     */
    protected $serviceUrl;

    /**
     * @var string[]
     */
    protected $whitelist;

    /**
     * @var integer
     */
    protected $serviceRequestFormat;

    /**
     * ImagesOptimizationServiceHTMLFilter constructor.
     *
     * @param ServiceSignature $signature
     * @param URL $baseUrl
     * @param URL $serviceUrl
     * @param string[] $whitelist
     * @param null|int $serviceRequestFormat
     */
    public function __construct(
        ServiceSignature $signature,
        Retriever $retriever,
        URL $baseUrl,
        URL $serviceUrl,
        array $whitelist,
        $serviceRequestFormat = null
    ) {
        $this->signature = $signature;
        $this->retriever = $retriever;
        $this->baseUrl = $baseUrl;
        $this->serviceUrl = $serviceUrl;
        $this->whitelist = $whitelist;
        $this->serviceRequestFormat = $serviceRequestFormat == ServiceRequest::FORMAT_PATH
                                    ? ServiceRequest::FORMAT_PATH
                                    : ServiceRequest::FORMAT_QUERY;
    }

    public function transformHTMLDOM(\Kibo\Phast\Common\DOMDocument $document) {
        /** @var \DOMElement $img */
        foreach ($document->query('//img') as $img) {
            $this->rewriteSrc($img);
            $this->rewriteSrcset($img);
        }
    }

    private function rewriteSrc(\DOMElement $img) {
        if (!($url = $this->shouldRewriteUrl($img->getAttribute('src')))) {
            return;
        }
        $params = ['src' => $url];
        foreach (['width', 'height'] as $attr) {
            $value = $img->getAttribute($attr);
            if (preg_match('/^[1-9][0-9]*$/', $value)) {
                $params[$attr] = $value;
            }
        }
        $img->setAttribute(
            'src',
            $this->makeSignedUrl($params)
        );
    }

    private function rewriteSrcset(\DOMElement $img) {
        $srcset = $img->getAttribute('srcset');
        if (!$srcset) {
            return;
        }
        $rewritten = preg_replace_callback('/([^,\s]+)(\s+(?:[^,]+))?/', function ($match) {
            if ($url = $this->shouldRewriteUrl($match[1])) {
                $params = ['src' => $url];
                $url = $this->makeSignedUrl($params);
            } else {
                $url = $match[1];
            }
            if (isset ($match[2])) {
                return $url . $match[2];
            }
            return $url;
        }, $srcset);
        $img->setAttribute('srcset', $rewritten);
    }

    protected function shouldRewriteUrl($url) {
        if (!$url || substr($url, 0, 5) === 'data:') {
            return;
        }
        $this->logger()->info('Rewriting img {url}', ['url' => $url]);
        $absolute = URL::fromString($url)->withBase($this->baseUrl)->toString();
        foreach ($this->whitelist as $pattern) {
            if (preg_match($pattern, $absolute)) {
                return $absolute;
            }
        }
    }

    protected function makeSignedUrl($params) {
        $modTime = $this->retriever->getLastModificationTime(URL::fromString($params['src']));
        if ($modTime) {
            $params['cacheMarker'] = $modTime;
        }
        return (new ServiceRequest())->withParams($params)
            ->withUrl($this->serviceUrl)
            ->sign($this->signature)
            ->serialize($this->serviceRequestFormat);
    }

}