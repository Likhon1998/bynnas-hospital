<?php

namespace Database\Seeders;

final class DoctorPhotos
{
    /**
     * Curated portrait URLs for demo doctors (Unsplash).
     *
     * @return list<string>
     */
    public static function portraits(): array
    {
        return [
            'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1651008376811-b90baee60c1f?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1582750433449-648ed127bb54?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1527613426441-4da17471b66d?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1612531386530-97286d97c2d2?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1607990283143-e81e7a2c9349?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1666214680933-528e4db0f686?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1550831107-1553ea73038b?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1582719471384-894fbb16e074?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?auto=format&fit=crop&w=800&q=80',
        ];
    }

    public static function forIndex(int $index): string
    {
        $portraits = self::portraits();

        return $portraits[$index % count($portraits)];
    }
}
