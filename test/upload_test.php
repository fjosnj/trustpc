<?php if (session_status()===PHP_SESSION_NONE) session_start(); ?>
<!doctype html><meta charset="utf-8"><title>Upload Test</title>
<form method="post" enctype="multipart/form-data">
  <input type="file" name="f"><button>UP</button>
</form>
<?php
if ($_FILES) { echo '<pre>'; var_dump($_FILES['f']); echo '</pre>'; }
