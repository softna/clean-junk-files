<?php namespace ProcessWire;

ini_set('max_execution_time', 60*5); // 5 minutes, increase as needed
include("./index.php");
$dir = new \DirectoryIterator(wire('config')->paths->files);

$clean_button = "";
?>

<form action="clean-files.php" method="POST">
    <h1 style="margin-bottom: 30px;">Junk Files Check</h1>
    <input type="submit" name="check" value="Run Check" />
    <?php if($input->post->check) :?>
        <input type="submit" name="clean" value="Clen Up Files" />
    <?php endif;?>
</form>

<?php

if($input->post->check || $input->post->clean) {

    foreach($dir as $file) {
      if($file->isDot() || !$file->isDir()) continue;
      $id = $file->getFilename();
      if(!ctype_digit("$id")) continue;
      $page = wire('pages')->get((int) $id);
      if(!$page->id) {
        echo "<div>Orphaned file: " . wire('config')->urls->files . "$id/" . $f->getBasename() . "</div>";
        continue;
    }
      // determine which files are valid for the page
      $valid = array();
      foreach($page->template->fieldgroup as $field) {
        if($field->type instanceof FieldtypeFile) {
            if(!empty($page->get($field->name))) {
              foreach($page->get($field->name) as $file) {
                $valid[] = $file->basename;
                if($field->type instanceof FieldtypeImage) {
                  foreach($file->getVariations() as $f) {
                    $valid[] = $f->basename;
                  }
                }
              }
            }
        }
      }
      // now find all the files present on the page
      // identify those that are not part of our $valid array
      $d = new \DirectoryIterator($page->filesManager->path);
      foreach($d as $f) {
        if($f->isDot() || !$f->isFile()) continue;
        if(!in_array($f->getFilename(), $valid)) {
          echo "<div>Orphaned file: " . wire('config')->urls->files . "$id/" . $f->getBasename() . "</div>";
          if($input->post->clean) {
              unlink($f->getPathname());
          }
        }
      }
      wire('pages')->uncache($page); // just in case we need the memory
    }

    if($input->post->clean) {
        header("Location: clean-files.php");
        exit();
    }

}

?>
