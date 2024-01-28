<?php
declare(strict_types=1);

namespace SetBased\Phing\Task;

use SetBased\Helper\ProgramExecution;

/**
 * Phing task for set the mtime of (source) file to the latest commit time in Git.
 */
class LastCommitTimeTask extends SetBasedTask
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The number of sources files found.
   *
   * @var int
   */
  private int $count = 0;

  /**
   * Array with last commit time for each file.
   *
   * @var array
   */
  private array $lastCommitTimes = [];

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
    $this->logInfo('Preserving last commit time under directory %s', $this->workDirName);

    $this->fetchAllFilesUnderGit();
    $this->fetchLastCommitTimes();
    $this->setFilesMtime();

    $this->logInfo('Found %d files', $this->count);
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
   * Fetches all files that are currently under git.
   */
  private function fetchAllFilesUnderGit(): void
  {
    $command = ['git', 'ls-files'];
    [$output, $return] = ProgramExecution::exec1($command, null);
    if ($return!==0)
    {
      $this->logError('Can not execute command %s ', implode(' ', $command));
    }

    foreach ($output as $filename)
    {
      $this->lastCommitTimes[$filename] = 0;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Fetches last commit time of each file in the Git repository.
   */
  private function fetchLastCommitTimes(): void
  {
    // Execute command for get list with file name and mtime from GIT log.
    $command = ['git', 'log', '--format=format:%ai', '--name-only'];
    [$output, $return] = ProgramExecution::exec1($command, null);
    if ($return!==0)
    {
      $this->logError('Can not execute command %s in exec', implode(' ', $command));
    }

    // Find latest mtime for each file from $output.
    // Note: Each line is either:
    //       * an empty line
    //       * a timestamp
    //       * a filename.
    $commit_date = '';
    foreach ($output as $line)
    {
      if ((preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} [+\-]\d{4}$/', $line)))
      {
        $commit_date = strtotime($line);
      }
      else
      {
        if ($line!=='')
        {
          $filename = $line;
          if (isset($this->lastCommitTimes[$filename]) && $this->lastCommitTimes[$filename]<$commit_date)
          {
            $this->lastCommitTimes[$filename] = $commit_date;
          }
        }
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Set last commit time to all files in build directory.
   */
  private function setFilesMtime(): void
  {
    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->workDirName,
                                                                            \FilesystemIterator::UNIX_PATHS));
    foreach ($files as $full_path => $file)
    {
      if ($file->isFile())
      {
        $key = substr($full_path, strlen($this->workDirName.'/'));
        if (isset($this->lastCommitTimes[$key]))
        {
          $this->logVerbose('Set mtime of %s to %s', $full_path, date('Y-m-d H:i:s', $this->lastCommitTimes[$key]));
          $success = touch($full_path, $this->lastCommitTimes[$key]);
          if (!$success)
          {
            $this->logError('Unable to set mtime of file %s', $full_path);
          }

          $this->count++;
        }
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
