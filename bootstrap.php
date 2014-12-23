<?php

require 'game.php';

$file = 'words.csv';

$game = new Game(5, $file);
$game->play();