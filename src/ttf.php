<?php

define('ROOT_DIR', __DIR__ . '/..');

require ROOT_DIR . '/vendor/autoload.php';

use Serafim\SDL\SDL;
use Serafim\SDL\TTF\TTF;
use Serafim\SDL\Event\Type;

const WINDOW_WIDTH = 300;
const WINDOW_HEIGHT = 200;

$sdl = new SDL(library: ROOT_DIR . '/lib/SDL2.dll');
$ttf = new TTF(sdl: $sdl, library: ROOT_DIR . '/lib/SDL2_ttf.dll');

$sdl->SDL_Init(SDL::SDL_INIT_EVERYTHING);
$ttf->TTF_Init();

$window = $sdl->SDL_CreateWindow(
    'An SDL2 window',
    SDL::SDL_WINDOWPOS_UNDEFINED,
    SDL::SDL_WINDOWPOS_UNDEFINED,
    WINDOW_WIDTH,
    WINDOW_HEIGHT,
    SDL::SDL_WINDOW_OPENGL
);

if ($window === null) {
    throw new \Exception(sprintf('Could not create window: %s', $sdl->SDL_GetError()));
}

$renderer = $sdl->SDL_CreateRenderer($window, -1, 0);
if (!$renderer) {
    throw new \Exception("Can't create renderer!");
}

$font = $ttf->TTF_OpenFont(ROOT_DIR . '/font/arial.ttf', 42);

echo 'Hinting: ' . $ttf->TTF_GetFontHinting($font) . "\n";
echo 'Kerning: ' . $ttf->TTF_GetFontKerning($font) . "\n";
echo 'Style:   ' . match($ttf->TTF_GetFontStyle($font)) {
    TTF::TTF_STYLE_NORMAL => 'normal',
    TTF::TTF_STYLE_BOLD => 'bold',
    TTF::TTF_STYLE_ITALIC => 'italic',
    TTF::TTF_STYLE_UNDERLINE => 'underline',
    TTF::TTF_STYLE_STRIKETHROUGH => 'strikethrough',
}   . "\n";


$color = $sdl->new('SDL_Color');
$color->r = 200;
$color->g = 0;
$color->b = 0;

$surface = $ttf->TTF_RenderText_Solid($font, 'Hello World!', FFI::addr($color));
$texture = $sdl->SDL_CreateTextureFromSurface($renderer, $surface);

$rect = $sdl->new('SDL_Rect');
$sdl->SDL_QueryTexture($texture, null, null, FFI::addr($rect->w), FFI::addr($rect->h));

// 设置文本纹理的位置
$rect->x = (WINDOW_WIDTH - $rect->w) / 2;
$rect->y = WINDOW_HEIGHT - $rect->h - 50;

$event = $sdl->new('SDL_Event');
$running = true;

while ($running) {
    if ($sdl->SDL_PollEvent(FFI::addr($event))) {
        if ($event->type === Type::SDL_QUIT) {
            $running = false;
        }
    } else {
        $sdl->SDL_SetRenderDrawColor($renderer, 255, 255, 255, 255);
        $sdl->SDL_RenderClear($renderer);
        $sdl->SDL_RenderCopy($renderer, $texture, null, FFI::addr($rect));
        $sdl->SDL_RenderPresent($renderer);
    }
}

$sdl->SDL_FreeSurface($surface);
$sdl->SDL_DestroyTexture($texture);
$sdl->SDL_DestroyWindow($window);
$ttf->TTF_Quit();
$sdl->SDL_Quit();
