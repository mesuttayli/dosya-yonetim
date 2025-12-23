<?php
/**
 * TechCode Admin - Çıkış
 */

session_start();
session_destroy();

header('Location: login.php');
exit;
