<?php
// Get category(label) data from main url
// Your blog address, example:
// https://example.blogspot.com/
// then $blogAddr = example;
$blogAddr = 'example';
$dirPath = './' . $blogAddr . 'RssJson';
// if there is no directory for json files then create one, otherwise just skip the creating directory.
if (!file_exists($dirPath)) {
    mkdir($dirPath, 0755, true);
}
$mainRssUrl = 'http://' . $blogAddr . '.blogspot.com/feeds/posts/default?alt=json&&max-results=0';
$mainJson = file_get_contents($mainRssUrl);
// we decode json to dimentional arrays
$mainJsonArry = json_decode($mainJson, true);
// Go through json data by category and save them as ae file
foreach($mainJsonArry['feed']['category'] as $categories => $category){
    $urlEncodedCategory = urlencode($category["term"]);
    $categoryRssUrl = 'http://' . $blogAddr . '.blogspot.com/feeds/posts/default/-/' . $urlEncodedCategory . '?alt=json&start-index=1&max-results=150';
    $categoryJson = file_get_contents($categoryRssUrl);
    $categoryJsonArry = json_decode($categoryJson, true);
    // if the category contain more than 150 posts, we go through whole posts that is more than 150
    if($categoryJsonArry['feed']['openSearch$totalResults']['$t'] > 150){
      $totalNumberOfPost = $categoryJsonArry['feed']['openSearch$totalResults']['$t'];
      $numberOfFiles = $totalNumberOfPost / 150;
      if(($totalNumberOfPost / 150) > (floor($totalNumberOfPost / 150))){
        $numberOfFiles = $numberOfFiles + 1;
      }
      for($i = 1; $i <= $numberOfFiles; $i++){
        $maxResult = $i * 150;
        $startIndex = 1;
        if($i > 1){
          $startIndex = 1 + (($i - 1) * 150);
        }
        $categoryRssUrl = 'http://' . $blogAddr . '.blogspot.com/feeds/posts/default/-/' . $urlEncodedCategory . '?alt=json&start-index=' . $startIndex . '&max-results=' . $maxResult;
        $categoryJson = file_get_contents($categoryRssUrl);
        $categoryJsonArry = json_decode($categoryJson, true);
        $fileIndex = sprintf("%04s", $i);
        file_put_contents($dirPath . '/' . $urlEncodedCategory . '_' . $fileIndex . '.json', $categoryJson);
      }
    }
    // number of total post is lower 150 therefore we do not have to do anything complicated.
    // simply save the one we have.
    else{
      file_put_contents($dirPath . '/' . $urlEncodedCategory . '_0001.json', $categoryJson);
    }
};
?>
