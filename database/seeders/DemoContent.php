<?php

namespace Database\Seeders;

final class DemoContent
{
    public const HOSPITAL_NAME = 'Bynnas Hospital';

    public const TAGLINE = 'Care Today • Healthier Tomorrow';

    public const LOGO = '/images/branding/bynnas-hospital-logo.png';

    public const LOGO_MARK = '/images/branding/bynnas-mark.png';

    public const DISCLAIMER = 'This is fictional demo content for local website development. It is general information only and is not medical advice. Please consult a qualified healthcare professional for personal medical questions.';

    public static function image(string $label, int $width = 1200, int $height = 800): string
    {
        $text = rawurlencode($label);

        return "https://placehold.co/{$width}x{$height}/0F5C6B/FFFFFF?text={$text}";
    }

    public static function articleBody(string $paragraph): string
    {
        return '<p>'.$paragraph.'</p><p>'.self::DISCLAIMER.'</p>';
    }
}
