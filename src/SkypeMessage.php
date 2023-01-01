<?php

namespace SkypeToTelegram;

class SkypeMessage
{
    public ?string $text = null;
    /** @var array<string> urls to images */
    public array $images = [];
}