<?php
// Legacy admin endpoint retired; permanently redirect to consolidated login.
header('Location: admin_login.php', true, 301);
exit();
