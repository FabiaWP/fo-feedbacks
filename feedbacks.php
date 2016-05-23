<?php
/*
Plugin Name: FeedBacks Fabia OOP
Plugin URI:

 */
define('FO_FEEDBACKS_PATH', dirname(__FILE__));
define('FO_FEEDBACKS_FOLDER', basename(FO_FEEDBACKS_PATH));
define('FO_FEEDBACKS_URL', plugins_url() . '/' . FO_FEEDBACKS_FOLDER);

require_once __DIR__.'/class-fo-feedbacks2.php';

new FO_FEEDBACKS();




