<?php
declare(strict_types=1);

namespace SetBased\Phing\Task;

/**
 * Phing task for setting recursively the mtime of a directories to the max mtime of its entries.
 */
class SetDirectoryMTimeTask extends SetBasedTask
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The number of subdirectories found.
   *
   * @var int
   */
  private int $count = 0;

  /**
   * The parent directory under which the mtime of (source) files must be set.
   *
   * @var string
   */
  private string $workDirName;

  //--------------------------------------------------------------------------------------------------------------------

  /**
   * Main method of this task.
   */
  public function main()
  {
    $this->logInfo('Setting mtime recursively under directory %s', $this->workDirName);

    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->workDirName,
                                                                               \FilesystemIterator::SKIP_DOTS),
                                               \RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($iterator as $path => $file_info)
    {
      if ($file_info->isDir())
      {
        $mtime = $this->getMaxMTime($path);
        if ($mtime!==null)
        {
          $this->logVerbose('Set mtime of %s to %s', $path, date('Y-m-d H:i:s', $mtime));
          $success = touch($path, $mtime);
          if (!$success)
          {
            $this->logError('Unable to set mtime of %s', $path);
          }

          $this->count++;
        }
      }
    }

    $this->logInfo('Found %d subdirectories', $this->count);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Setter for XML attribute dir.
   *
   * @param string $workDirName The name of the working directory.
   */
  public function setDir(string $workDirName): void
  {
    $this->workDirName = $workDirName;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Get max mtime from all files in directory.
   *
   * @param string $dirName The name of the directory.
   *
   * @return int|null
   */
  private function getMaxMTime(string $dirName): ?int
  {
    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($dirName, \FilesystemIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::SELF_FIRST);

    $mtime = null;
    foreach ($iterator as $file_info)
    {
      $mtime = max($mtime, $file_info->getMTime());
    }

    return $mtime;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
