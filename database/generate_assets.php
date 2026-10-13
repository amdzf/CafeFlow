<?php

$items = [
    'kopi-susu.jpg' => ['title' => 'Kopi Susu Aren', 'bg' => '#4A2E18', 'icon' => '☕', 'accent' => '#D4A373'],
    'americano.jpg' => ['title' => 'Iced Americano', 'bg' => '#2B170B', 'icon' => '🧊', 'accent' => '#8D5B4C'],
    'latte.jpg' => ['title' => 'Caffe Latte', 'bg' => '#6F4E37', 'icon' => '☕', 'accent' => '#F3E5AB'],
    'caramel-macchiato.jpg' => ['title' => 'Caramel Macchiato', 'bg' => '#795548', 'icon' => '🍮', 'accent' => '#D7CCC8'],
    'matcha-latte.jpg' => ['title' => 'Matcha Latte', 'bg' => '#2E7D32', 'icon' => '🍵', 'accent' => '#C8E6C9'],
    'earl-grey.jpg' => ['title' => 'Earl Grey Tea', 'bg' => '#D84315', 'icon' => '🫖', 'accent' => '#FFCCBC'],
    'nasgor-spesial.jpg' => ['title' => 'Nasi Goreng', 'bg' => '#E65100', 'icon' => '🍳', 'accent' => '#FFE0B2'],
    'katsu-curry.jpg' => ['title' => 'Chicken Katsu', 'bg' => '#F57C00', 'icon' => '🍛', 'accent' => '#FFF3E0'],
    'french-fries.jpg' => ['title' => 'Truffle Fries', 'bg' => '#FBC02D', 'icon' => '🍟', 'accent' => '#FFF9C4'],
    'chicken-wings.jpg' => ['title' => 'Crispy Wings', 'bg' => '#C2185B', 'icon' => '🍗', 'accent' => '#F8BBD0'],
    'croissant.jpg' => ['title' => 'Almond Croissant', 'bg' => '#8D6E63', 'icon' => '🥐', 'accent' => '#EFEBE9'],
    'cheesecake.jpg' => ['title' => 'Burnt Cheesecake', 'bg' => '#F57F17', 'icon' => '🧀', 'accent' => '#FFFDE7']
];

$dir = dirname(__DIR__) . '/public/images/products/';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

foreach ($items as $filename => $info) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">
      <defs>
        <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="' . $info['bg'] . '"/>
          <stop offset="100%" stop-color="' . $info['accent'] . '"/>
        </linearGradient>
      </defs>
      <rect width="100%" height="100%" fill="url(#g)"/>
      <circle cx="200" cy="125" r="60" fill="rgba(255,255,255,0.2)"/>
      <text x="200" y="148" font-size="56" text-anchor="middle">' . $info['icon'] . '</text>
      <text x="200" y="235" font-family="Arial, sans-serif" font-weight="bold" font-size="20" fill="#FFFFFF" text-anchor="middle" letter-spacing="0.5">' . $info['title'] . '</text>
    </svg>';
    file_put_contents($dir . $filename, $svg);
}

echo "All 12 product SVGs created in {$dir}\n";
