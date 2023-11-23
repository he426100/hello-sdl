<?php

define('ROOT_DIR', __DIR__ . '/..');

require ROOT_DIR . '/vendor/autoload.php';

use Serafim\SDL\SDL;
use Serafim\SDL\MessageBox\Flags;

$sdl = new SDL(library: ROOT_DIR . '/lib/SDL2.dll');

$sdl->SDL_Init(SDL::SDL_INIT_EVERYTHING);
$sdl->SDL_ShowSimpleMessageBox(Flags::SDL_MESSAGEBOX_INFORMATION, 'Hello SDL', '再见了！', null);
$sdl->SDL_Quit();
