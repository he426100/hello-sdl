<?php

require __DIR__ . '/vendor/autoload.php';

use Serafim\SDL\SDL;
use Serafim\SDL\Image\Image;
use Serafim\SDL\Event\Type;

class HelloSDL
{
    const WINDOW_WIDTH = 300;
    const WINDOW_HEIGHT = 300;
    const DELAY_TIME = 30;

    private $sdl;
    private $image;
    private $mainWindow;
    private $renderer;
    private $surface;
    private $texture;
    private $event;
    private $imgRect;
    private $destRect;
    private $isQuit;

    public function run(): int
    {
        try {
            $this->init();
        } catch (\Throwable $e) {
            $this->sdl->SDL_Log('Init faliled');
            $this->sdl->SDL_Log($e->getMessage());
            return -1;
        }
        $this->sdl->SDL_Log('Init success');

        while (!$this->isQuit) {
            $this->renderScreen();
            $this->update();
            $this->sdl->SDL_Delay(self::DELAY_TIME);
        }
        
        $this->sdl->SDL_Log('Window is quit');
        $this->clean();
        $this->sdl->SDL_Log('SDL quit');
        return 0;
    }
    
    private function init(): void
    {
        $this->sdl = new SDL();
        $this->image = new Image();
        $this->event = $this->sdl->new('SDL_Event');

        if ($this->sdl->SDL_Init(SDL::SDL_INIT_EVERYTHING) < 0) {
            throw new \Exception("SDL2 can't be init!");
        }
        $this->mainWindow = $this->sdl->SDL_CreateWindow("hello world", SDL::SDL_WINDOWPOS_UNDEFINED, SDL::SDL_WINDOWPOS_UNDEFINED, self::WINDOW_WIDTH, self::WINDOW_HEIGHT, SDL::SDL_WINDOW_OPENGL);
        if (!$this->mainWindow) {
            throw new \Exception("Can't create window!");
        }

        $this->renderer = $this->sdl->SDL_CreateRenderer($this->mainWindow, -1, 0);
        if (!$this->renderer) {
            throw new \Exception("Can't create renderer!");
        }

        $this->surface = $this->image->IMG_Load("2.jpg");
        if (!$this->surface) {
            $this->sdl->SDL_Log("img is not loaded");
            throw new \Exception("2.jpg is not found");
        }
        $this->imgRect = $this->sdl->new('SDL_Rect');
        $this->texture = $this->sdl->SDL_CreateTextureFromSurface($this->renderer, $this->surface);
        $this->sdl->SDL_QueryTexture($this->texture, null, null, FFI::addr($this->imgRect->w), FFI::addr($this->imgRect->h));
        $this->sdl->SDL_Log("the image width and height is: {$this->imgRect->w}, {$this->imgRect->h}");

        $this->destRect = $this->imgRect;
        $this->destRect->x = $this->destRect->y = 0;
    }

    private function renderScreen()
    {
        $this->sdl->SDL_RenderClear($this->renderer);
        $this->sdl->SDL_SetRenderDrawColor($this->renderer, 0, 200, 0, 255);
    }

    private function handleEvent()
    {
        while ($this->sdl->SDL_PollEvent(FFI::addr($this->event))) {
            if ($this->event->type === Type::SDL_QUIT) {
                $this->isQuit = true;
            }
        }
    }

    private function update()
    {
        $this->handleEvent();

        $this->sdl->SDL_RenderCopy($this->renderer, $this->texture, FFI::addr($this->imgRect), FFI::addr($this->destRect));
        $this->sdl->SDL_RenderPresent($this->renderer);
    }

    private function clean()
    {
        $this->sdl->SDL_FreeSurface($this->surface);
        $this->sdl->SDL_DestroyTexture($this->texture);
        $this->sdl->SDL_DestroyWindow($this->mainWindow);
        $this->sdl->SDL_Quit();
    }
}

$hello = new HelloSDL();
exit ($hello->run());
