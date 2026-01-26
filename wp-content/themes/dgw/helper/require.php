<?php

require_once (DIR_CONTROLLER . 'controller.php');
new Controller_Main();

require_once (DIR_METABOX . 'metabox.php');
new Metabox_Main();

require_once (DIR_TAXONOMY . 'taxonomy.php');
new Taxonomy_Main();
