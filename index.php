<?php
session_start();
$view = new stdClass();
$view->title = "Home Page";

// Show the home page view
require_once('Views/index.phtml');