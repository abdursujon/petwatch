<?php
require_once('../Models/Database.php');

$db = Database::getInstance();
$dbHandle = $db->getdbConnection();

$locations = [
    'near the park entrance', 'by the bus stop on Oxford Road', 'outside Tesco Express',
    'near Piccadilly train station', 'by the canal towpath', 'in the alley behind the pub',
    'near the school playground', 'outside the post office', 'by the church on Main Street',
    'at the roundabout near Aldi', 'in the car park behind Asda', 'near the football pitch',
    'by the river bridge', 'outside the chippy on High Street', 'near the library entrance',
    'at the crossroads by the petrol station', 'in the garden of a terraced house',
    'by the bins behind the takeaway', 'near the allotments', 'outside the vet clinic',
    'by the bench in the town square', 'near the train tracks', 'at the edge of the woods',
    'outside the corner shop', 'by the lake in the park', 'near the bus shelter',
    'in the field behind the houses', 'outside the pharmacy', 'by the war memorial',
    'near the skate park'
];

$actions = [
    'Spotted', 'Seen', 'Noticed', 'Found wandering', 'Caught a glimpse of it',
    'Saw it running', 'Saw it sitting', 'Seen resting', 'Spotted it hiding',
    'Noticed it sniffing around', 'Saw it eating something', 'Found it sleeping',
    'Seen barking at pigeons', 'Spotted it crossing the road', 'Noticed it following a jogger',
    'Saw it digging in the garden', 'Found it sheltering from the rain',
    'Seen chasing a squirrel', 'Spotted it limping slightly', 'Noticed it looking lost'
];

$times = [
    'early this morning', 'around noon', 'late afternoon', 'just before sunset',
    'around 8am', 'at lunchtime', 'mid-morning', 'in the evening',
    'just after dark', 'around 3pm', 'at dawn', 'late last night'
];

$extras = [
    'Looked healthy.', 'Seemed scared.', 'Was friendly when approached.', 'Ran away quickly.',
    'Had a collar on.', 'No collar visible.', 'Looked well fed.', 'Seemed hungry.',
    'Was limping.', 'Looked tired.', 'Very energetic.', 'Stayed in the same spot for a while.',
    'Approached me for food.', 'Was with another animal.', 'Alone and confused.',
    'Kept barking.', 'Very quiet.', 'Seemed domesticated.', 'Was shivering.',
    'Looked like it had been out for days.'
];

$stmt = $dbHandle->prepare("INSERT INTO sightings (pet_id, user_id, comment) VALUES (?, ?, ?)");

for ($i = 1; $i <= 1000; $i++) {
    $comment = $actions[array_rand($actions)] . ' ' .
        $locations[array_rand($locations)] . ' ' .
        $times[array_rand($times)] . '. ' .
        $extras[array_rand($extras)];
    $stmt->execute([$i, rand(1, 1002), $comment]);
}

echo "Done! Inserted 1000 sightings.\n";
