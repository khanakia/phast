<?php require '_.php'; ?>
<!doctype html>
<html>
<body>
<script async src="script_order.script.php?name=async+external&sleep=200"></script>
<script defer src="script_order.script.php?name=deferred+external"></script>
<script>
    order = window.order || [];
    order.push('inline');
</script>
<script src="script_order.script.php?name=synchronous+external"></script>
<script>
    order = window.order || [];
    order.push('second inline');
</script>
</body>
</html>
