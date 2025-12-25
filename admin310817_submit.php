<?php
// Legacy admin submission endpoint retired; permanently redirect to consolidated login handler.
header('Location: admin_login.php', true, 301);
exit();
