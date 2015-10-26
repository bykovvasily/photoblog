<?php
//add conf
require('conf.php');

//functions for getting content
function getFlickrPhotos($search) {
    global $userId, $apiKey, $perPage;

    $searchUrl = 'http://flickr.com/services/rest/?method=flickr.photos.search&api_key=' . $apiKey . '&user_id=' . $userId . '&tags=' . $search . '&extras=date_upload&per_page=' . $perPage . '&page=1&format=php_serial';

    return file_get_contents($searchUrl);
}

function getFlickrSetList() {
    global $userId, $apiKey, $perPage;

    $getPhotoListUrl = 'http://flickr.com/services/rest/?method=flickr.photosets.getList&api_key=' . $apiKey . '&user_id=' . $userId . '&per_page=' . $perPage . '&page=1&format=php_serial';

    return file_get_contents($getPhotoListUrl);
}

function getSetPhotos($setId) {
    global $apiKey, $perPage;

    $getPhotoListUrl = 'http://flickr.com/services/rest/?method=flickr.photosets.getPhotos&api_key=' . $apiKey . '&photoset_id=' . $setId . '&extras=date_upload&per_page=' . $perPage . '&page=1&format=php_serial';

    $returnArray = unserialize(file_get_contents($getPhotoListUrl)); unset($returnArray['stat']);
    return $returnArray;
}

//Create all content arrays
$arraFlickrPhotos = Array(
    "versionStamp" => time(),
    "content" => Array(
        "photostream" => getFlickrPhotos('portrait%2C+street%2C+space'),
        "portrait" => getFlickrPhotos('portrait'),
        "street" => getFlickrPhotos('street'),
        "space" => getFlickrPhotos('space')
    )
);
$arraySetList = unserialize(getFlickrSetList());

//
//comparison photos and sets
//

//create photos id list with set, and add in setList photos from this set
$searchCoincidenceArray = Array();
foreach ($arraySetList['photosets']['photoset'] as $key => $value) {

    $arrayPhotosBySet = getSetPhotos($value['id']);
    $setTitle = $value['title']['_content'];
    $setId = $value['id'];

    //add photos to set, in sets array
    $arraySetList['photosets']['photoset'][$key]['photo'] = $arrayPhotosBySet['photoset']['photo'];

    foreach ($arrayPhotosBySet as $key => $value) {
        foreach ($value['photo'] as $key => $value) {
            $searchPhotoId = $value['id'];
            $searchCoincidenceArray[$searchPhotoId] = Array();
            $searchCoincidenceArray[$searchPhotoId]['setId'] = $setId;
            $searchCoincidenceArray[$searchPhotoId]['setTitle'] = $setTitle;
        }
    }
}

//add setUrl to all photos
$searchResult = false;
foreach ($arraFlickrPhotos['content'] as $key => $value) {
    $thisTagContent = $key;
    $arraFlickrPhotos['content'][$thisTagContent] = unserialize($value);

    foreach ($arraFlickrPhotos['content'][$thisTagContent]['photos']['photo'] as $key => $value) {
        $searchResult = array_key_exists($value['id'], $searchCoincidenceArray);

        if ($searchResult AND !$searchResult{0}) {
            $arraFlickrPhotos['content'][$thisTagContent]['photos']['photo'][$key]['setid'] = $searchCoincidenceArray[$value['id']]['setId'];
            $arraFlickrPhotos['content'][$thisTagContent]['photos']['photo'][$key]['settitle'] = $searchCoincidenceArray[$value['id']]['setTitle'];
        }
    }
}

//edit set list array
$setsArray = Array();
foreach ($arraySetList['photosets']['photoset'] as $key => $value) {
    $setsArray[$value['id']] = $value;

    $setsArray[$value['id']]['photos'] = Array();
    $setsArray[$value['id']]['photos']['photo'] = $value['photo'];
    $setsArray[$value['id']]['photos']['total'] = count($value['photo']);

    //kill trash
    unset($setsArray[$value['id']]['photo']);
}

//find last uploaded photo in set
foreach ($setsArray as $key => $value) {
    $tempDateupload = 0;
    foreach ($setsArray[$key]['photos']['photo'] as $photo => $set) {
        if ($tempDateupload < $set['dateupload']) {
            $tempDateupload = $set['dateupload'];
        }
    }
    $setsArray[$value['id']]['date_last_upload'] = $tempDateupload;
}

//sort set array by last upload
//create list of last upload with richr order
$sortDataLastUpload = Array();
foreach ($setsArray as $key => $value) {
    $sortDataLastUpload[] = $value['date_last_upload'];
}

//sort indexes
rsort($sortDataLastUpload);

$arraySetsReOrder = Array();
//edit set order
foreach ($sortDataLastUpload as $key => $value) {
    $searchIndex = $value;
    $setId = '';
    foreach ($setsArray as $key => $value) {
        if (in_array($searchIndex, $value)) {
            $setId = $key;
            break;
        }
    }
    $arraySetsReOrder[$setId] = $setsArray[$setId];
}

//merge tags list and set list
$arraFlickrPhotos['content'] = $arraFlickrPhotos['content'] + $arraySetsReOrder;

//
//write in static files
//
file_put_contents($filePhpData, serialize($arraFlickrPhotos), LOCK_EX);
file_put_contents($fileJsonData, json_encode($arraFlickrPhotos['content']), LOCK_EX);

//testing
$testArray = unserialize(file_get_contents($filePhpData));

echo '<pre style="color: green;">';
echo '<b>Dump:</b><br /><br />';
var_dump($arraFlickrPhotos);
echo '</pre>';
?>