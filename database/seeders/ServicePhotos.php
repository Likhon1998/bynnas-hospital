<?php

namespace Database\Seeders;

final class ServicePhotos
{
    /**
     * @return array<string, string>
     */
    public static function bySlug(): array
    {
        return [
            'emergency-care' => 'https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?auto=format&fit=crop&w=1400&q=80',
            'icu' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1400&q=80',
            'ccu' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1400&q=80',
            'nicu' => 'https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=1400&q=80',
            'ambulance' => 'https://images.unsplash.com/photo-1587745416684-47953f16f02f?auto=format&fit=crop&w=1400&q=80',
            'laboratory' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?auto=format&fit=crop&w=1400&q=80',
            'ecg' => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?auto=format&fit=crop&w=1400&q=80',
            'x-ray' => 'https://images.unsplash.com/photo-1530497610245-94d3c16cda28?auto=format&fit=crop&w=1400&q=80',
            'ultrasound' => 'https://images.unsplash.com/photo-1551190822-a9333d879b1f?auto=format&fit=crop&w=1400&q=80',
            'ct-scan' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=1400&q=80',
            'mri' => 'https://images.unsplash.com/photo-1581594693702-fbdc51b2763b?auto=format&fit=crop&w=1400&q=80',
            'physiotherapy' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1400&q=80',
            'pharmacy' => 'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?auto=format&fit=crop&w=1400&q=80',
            'blood-bank' => 'https://images.unsplash.com/photo-1615461066159-fea0960485d5?auto=format&fit=crop&w=1400&q=80',
            'health-checkup' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=1400&q=80',
        ];
    }

    public static function forSlug(string $slug, int $fallbackIndex = 0): string
    {
        $map = self::bySlug();

        if (isset($map[$slug])) {
            return $map[$slug];
        }

        $fallback = array_values($map);

        return $fallback[$fallbackIndex % count($fallback)];
    }
}
