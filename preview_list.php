<?php
    header('Content-Type: text/html; charset=UTF-8');
    include('app/app.php');
    $clean = strip_tags_deep($_GET);
    if (!(isset($clean['listid']))) {
        die('Sorry - an error occured.');
    }
    $listmetadata = getReadingListPreviewMetadata($c,$clean['listid']);
    $listreadings = getReadingListPreview($c,$clean['listid']);
?>
<html>
    <head>
        <title>Preview a List: <?php echo $listmetadata['course'] . ': '.$listmetadata['linklabel']; ?></title>
        <link rel="stylesheet" href="web/styles.css" type="text/css" media="screen" />
    </head>
    <body>
        <h2 style="font-weight:bold; margin-top:20px;width:100%;text-align: center;bottom-border:thick solid black;"><?php echo $listmetadata['course'] . ': '.$listmetadata['linklabel']; ?></h2>
        <?php
          foreach ($listreadings as $reading) {
            ?>
              <div class="readingListLink">
                <h3><?php if ($reading['type'] == '3') { echo $reading['instruct']; } else { echo $reading['title']; } ?></h3>
                <p class="notes"><?php echo html_entity_decode($reading['notes']); ?></p>
              </div>
            <?php
          }
        ?>
    </body>
</html>