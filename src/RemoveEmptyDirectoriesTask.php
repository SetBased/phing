<?php
declare(strict_types=1);

namespace SetBased\Phing\Task;

use Phing\Exception\BuildException;

/**
 * Phing task for removing recursively empty directories.
 */
class RemoveEmptyDirectoriesTask extends SetBasedTask
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The number of empty directories removed.
   *
   * @var int
   */
  private int $count = 0;

  /**
   * If set the parent directory must be removed too (if empty).
   *
   * @var bool
   */
  private bool $removeParent = false;

  /**
   * The parent directory under which all empty directories must be removed.
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
    $this->logInfo('Removing empty directories under %s', $this->workDirName);

    $empty = $this->removeEmptyDirs($this->workDirName);
    if ($empty && $this->removeParent)
    {
      $this->removeDir($this->workDirName);
    }

    $this->logInfo('Removed %d empty directories', $this->count);
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
   * Setter for XML attribute removeParent.
   *
   * @param bool $removeParent If set the parent directory must be removed too (if empty).
   */
  public function setRemoveParent(bool $removeParent): void
  {
    $this->removeParent = $removeParent;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Removes a directory and does housekeeping.
   *
   * @param string $dir The directory to be removed.
   */
  private function removeDir(string $dir): void
  {
    $this->logVerbose('Removing %s', $dir);

    $suc = rmdir($dir);
    if ($suc===false) $this->logError('Unable to remove directory %s', $dir);

    $this->count++;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Removes recursively empty directories under a parent directory.
   *
   * @param string $parentDir The parent directory.
   *
   * @return bool True if the parent directory is empty. Otherwise, false.
   *
   * @throws BuildException
   */
  private function removeEmptyDirs(string $parentDir): bool
  {
    $entries = scandir($parentDir, SCANDIR_SORT_ASCENDING);
    if ($entries===false) $this->logError('Unable to scan directory %s', $parentDir);

    foreach ($entries as $i => $entry)
    {
      $path = $parentDir.'/'.$entry;

      if ($entry=='.' || $entry=='..')
      {
        unset($entries[$i]);
      }
      elseif (is_dir($path))
      {
        $empty = $this->removeEmptyDirs($path);
        if ($empty)
        {
          unset($entries[$i]);

          $this->removeDir($path);
        }
      }
    }

    return empty($entries);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
