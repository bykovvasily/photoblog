<?php
    //get content
    $unserializedArrayData = unserialize(file_get_contents('phpData.txt'));

    //main content
    $versionStamp = $unserializedArrayData['versionStamp'];
    $arrayData = $unserializedArrayData['content'];

    //create arrays of catalog and pages names
    $contentCatalogNamesList = Array();
    foreach ($arrayData as $key => $value) {
        array_push($contentCatalogNamesList, $key);
    }
    $pageNamesList = Array('sets', 'about');
?>