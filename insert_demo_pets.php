<?php
// ============================================
// INSERT DEMO PETS
// Step 1: Put this file in your project folder
// Step 2: Make sure you are logged in first
// Step 3: Visit: http://localhost/yourproject/insert_demo_pets.php
// Step 4: Delete this file after running it!
// ============================================
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    die("Please log in first, then visit this page again.");
}

$owner = $_SESSION['user'];

$pets = [
    ['Buddy',   'Dog', 2, 'Labrador Retriever', 'Buddy is a playful and energetic Labrador who loves to fetch and swim. Great with kids!',               'https://images.unsplash.com/photo-1560807707-8cc77767d783?w=400'],
    ['Max',     'Dog', 4, 'German Shepherd',    'Max is a loyal and intelligent dog. He is well-trained and very protective of his family.',             'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?w=400'],
    ['Charlie', 'Dog', 1, 'Golden Retriever',   'Charlie is a sweet and gentle puppy who loves cuddles and playing in the park.',                        'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=400'],
    ['Rocky',   'Dog', 3, 'Beagle',             'Rocky is a curious and friendly Beagle who loves going on walks and sniffing everything.',              'https://images.unsplash.com/photo-1505628346881-b72b27e84530?w=400'],
    ['Cooper',  'Dog', 5, 'Siberian Husky',     'Cooper is a beautiful Husky who loves cold weather and long outdoor adventures.',                       'https://images.unsplash.com/photo-1617895153857-82fe0c4f8def?w=400'],
    ['Luna',    'Cat', 2, 'Persian',            'Luna is a calm and elegant Persian cat who loves quiet environments and gentle cuddles.',                'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400'],
    ['Milo',    'Cat', 1, 'Siamese',            'Milo is a talkative and social Siamese kitten. He loves to follow you around the house.',               'https://images.unsplash.com/photo-1513360371489-6b4e2b3e6826?w=400'],
    ['Bella',   'Cat', 3, 'Maine Coon',         'Bella is a fluffy and friendly Maine Coon who gets along well with other pets.',                        'https://images.unsplash.com/photo-1568572933382-74d440642117?w=400'],
    ['Oliver',  'Cat', 4, 'British Shorthair',  'Oliver is a laid-back and gentle cat. Perfect for apartment living.',                                   'https://images.unsplash.com/photo-1533743983669-94fa5c4338ec?w=400'],
    ['Cleo',    'Cat', 2, 'Ragdoll',            'Cleo is an affectionate Ragdoll who loves being held and will follow you everywhere.',                   'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400'],
];

$stmt = $conn->prepare("INSERT INTO pets (name, type, age, breed, bio, image, status, owner) VALUES (?, ?, ?, ?, ?, ?, 'Available', ?)");

$count = 0;
foreach ($pets as $pet) {
    $stmt->bind_param("ssissss", $pet[0], $pet[1], $pet[2], $pet[3], $pet[4], $pet[5], $owner);
    if ($stmt->execute()) $count++;
}

echo "<h2>✅ Done! $count demo pets added successfully under username: <b>$owner</b></h2>";
echo "<p><a href='adopt-now.php'>Go to Adopt Now →</a></p>";
echo "<p style='color:red;'><b>Important: Delete this file (insert_demo_pets.php) now!</b></p>";
?>
