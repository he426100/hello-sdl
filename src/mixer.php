<?php

define('ROOT_DIR', __DIR__ . '/..');

require ROOT_DIR . '/vendor/autoload.php';

use Serafim\SDL\SDL;
use Serafim\SDL\Mixer\Mixer;
use Serafim\SDL\Event\Type;
use Serafim\SDL\Mixer\InitFlags;

$sdl = new SDL(library: ROOT_DIR . '/lib/SDL2.dll');
$mixer = new Mixer(sdl: $sdl, library: ROOT_DIR . '/lib/SDL2_mixer.dll');

$sdl->SDL_Init(SDL::SDL_INIT_EVERYTHING);
$mixer->Mix_Init(InitFlags::MIX_INIT_MP3);

$window = $sdl->SDL_CreateWindow(
    'An SDL2 window',
    SDL::SDL_WINDOWPOS_UNDEFINED,
    SDL::SDL_WINDOWPOS_UNDEFINED,
    640,
    480,
    SDL::SDL_WINDOW_OPENGL
);

if ($window === null) {
    throw new \Exception(sprintf('Could not create window: %s', $sdl->SDL_GetError()));
}

$mixer->Mix_OpenAudio(44100, SDL::AUDIO_S16SYS, 2, 2048);
$music = $mixer->Mix_LoadMUS('F:\音乐\去年夏天.mp3');
$mixer->Mix_PlayMusic($music, 0);

$event = $sdl->new('SDL_Event');
$running = true;

while ($running) {
    $sdl->SDL_PollEvent(FFI::addr($event));
    if ($event->type === Type::SDL_QUIT) {
        $running = false;
    }
}

$sdl->SDL_DestroyWindow($window);
$mixer->Mix_Quit();
$sdl->SDL_Quit();
