<?php

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function password_is_strong($password) {
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

function category_image($row) {
    $fallbacks = [
        'Amagwinya' => 'https://iol-prod.appspot.com/image/0a866824961ebe8a09ad8875ebac339f70fdbe4e=w700',
        'Pap & Stew' => 'https://th.bing.com/th/id/R.79bd82699014d8e920c92c40ef5436ff?rik=raN2D5pdTbIujQ&pid=ImgRaw&r=0',
        'Grilled Meat' => 'https://www.suburbansimplicity.com/wp-content/uploads/2021/06/How-to-keep-meat-moist-on-the-grill.jpg',
        'Breakfast' => 'https://tse3.mm.bing.net/th/id/OIP.cV8IfMXFn2uqn3YOR4ne0gHaHa?r=0&pid=ImgDetMain',
        'Beverages' => 'https://th.bing.com/th/id/R.4327e9e3d10634e6af86b81314bacd0d?rik=9VdoAm28zgLKsQ&pid=ImgRaw&r=0',
        'Vetkoek' => 'https://as2.ftcdn.net/v2/jpg/02/23/81/47/1000_F_223814741_k90kjLiXIFbLXpUtlnlOWyioTUoMt1vU.jpg',
        'Umngqusho' => 'https://www.thesouthafrican.com/wp-content/uploads/2020/07/087f68fa-umgquasho-samp-and-beans-with-lamb-and-chakalaka.jpg',
        'Snacks & Sides' => 'https://healy-group.com/wp-content/uploads/AdobeStock_953274304-min-1920x1076.jpeg',
        'Smiley & Walkie Talkies' => 'https://www.houseofyork.co.za/images/cmsimages/big/news-288-2588-walkie-talkie.jpeg',
        'Bunny Chow' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d',
    ];

    if (!empty($row['image_url'])) return $row['image_url'];
    if (!empty($row['category_image_url'])) return $row['category_image_url'];
    if (!empty($row['category_name']) && isset($fallbacks[$row['category_name']])) return $fallbacks[$row['category_name']];
    return 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d';
}

function seller_payout_amount($lineTotal) {
    return round($lineTotal * 0.90, 2);
}

function platform_fee_amount($lineTotal) {
    return round($lineTotal * 0.10, 2);
}
