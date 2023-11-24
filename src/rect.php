<?php

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('memory_limit', '1G');

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

define('ROOT_DIR', __DIR__ . '/..');

require ROOT_DIR . '/vendor/autoload.php';

use Serafim\SDL\SDL;
use Serafim\SDL\TTF\TTF;
use Serafim\SDL\Event\Type;
use Serafim\SDL\MessageBox\Flags;

const WINDOW_WIDTH = 300;
const WINDOW_HEIGHT = 300;

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

$font = $ttf->TTF_OpenFont(ROOT_DIR . '/font/msyh.ttc', 24);
$color = $sdl->new('SDL_Color');
$color->r = 0x0;
$color->g = 0x8C;
$color->b = 0xC8;

$rect = $sdl->new('SDL_Rect'); // 长方形，原点在左上角
$rect->w = 50;
$rect->h = 50;

$texture = $sdl->SDL_CreateTexture(
    $renderer,
    SDL::SDL_PIXELFORMAT_RGBA8888,
    SDL::SDL_TEXTUREACCESS_TARGET,
    WINDOW_WIDTH,
    WINDOW_HEIGHT,
); //创建纹理

$event = $sdl->new('SDL_Event');
$running = true;

while ($running) {
    if ($sdl->SDL_PollEvent(FFI::addr($event))) {
        if ($event->type === Type::SDL_QUIT) {
            $running = false;
        } elseif ($event->type === Type::SDL_MOUSEBUTTONDOWN) {
            $point = $sdl->new('SDL_Point');
            $point->x = $event->button->x;
            $point->y = $event->button->y;

            // 无法使用 $sdl->SDL_PointInRect
            if (SDL_PointInRect($point, $rect)) {
                $sdl->SDL_ShowSimpleMessageBox(Flags::SDL_MESSAGEBOX_INFORMATION, 'Hello SDL', '干嘛？', null);
            }
        }
        continue;
    }
    if (!(time() % 5)) {
        $rect->x = rand(0, WINDOW_WIDTH - $rect->w);
        $rect->y = rand(0, WINDOW_HEIGHT - $rect->h);
    }
    $sdl->SDL_SetRenderTarget($renderer, $texture); // 设置渲染目标为纹理
    $sdl->SDL_SetRenderDrawColor($renderer, 0, 0, 0, 0); // 纹理背景为黑色
    $sdl->SDL_RenderClear($renderer); // 清屏

    $sdl->SDL_RenderDrawRect($renderer, FFI::addr($rect)); // 绘制一个长方形
    $sdl->SDL_SetRenderDrawColor($renderer, 255, 255, 255, 255); // 长方形为白色
    $sdl->SDL_RenderFillRect($renderer, FFI::addr($rect));

    $textSurface = $ttf->TTF_RenderUTF8_Solid($font, '好', $color); // 渲染文本
    $textTexture = $sdl->SDL_CreateTextureFromSurface($renderer, $textSurface); // 从文本表面创建纹理

    $textRect = $sdl->new('SDL_Rect'); // 文字
    $sdl->SDL_QueryTexture($textTexture, null, null, FFI::addr($textRect->w), FFI::addr($textRect->h));
    $textRect->x = $rect->x + ($rect->w - $textRect->w) / 2;
    $textRect->y = $rect->y + ($rect->h - $textRect->h) / 2;

    $sdl->SDL_FreeSurface($textSurface); // 释放文本表面

    $sdl->SDL_RenderCopy($renderer, $textTexture, null, FFI::addr($textRect)); // 拷贝文本纹理到长方形内
    $sdl->SDL_DestroyTexture($textTexture); // 销毁文本纹理

    $sdl->SDL_SetRenderTarget($renderer, null); // 恢复默认，渲染目标为窗口
    $sdl->SDL_RenderCopy($renderer, $texture, null, null); // 拷贝纹理到CPU

    $sdl->SDL_RenderPresent($renderer); // 输出到目标窗口上
}

$sdl->SDL_DestroyTexture($texture);
$sdl->SDL_DestroyRenderer($renderer);
$sdl->SDL_DestroyWindow($window);
$sdl->SDL_Quit();

function SDL_PointInRect($point, $rect)
{
    return (($point->x >= $rect->x) && ($point->x < ($rect->x + $rect->w)) &&
        ($point->y >= $rect->y) && ($point->y < ($rect->y + $rect->h)));
}
