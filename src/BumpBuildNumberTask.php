<?php
declare(strict_types=1);

namespace SetBased\Phing\Task;

/**
 * Phing task for bumping build numbers.
 */
class BumpBuildNumberTask extends SetBasedTask
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The build number.
   *
   * @var int|null
   */
  private ?int $buildNumber = null;

  /**
   * The name of variable holding the build number.
   *
   * @var string|null
   */
  private ?string $buildNumberProperty = null;

  /**
   * The name of the file containing the current build number.
   *
   * @var string|null
   */
  private ?string $filename = null;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Main method of this task.
   */
  public function main(): void
  {
    $this->readBuildNumber();
    $this->bumpBuildNumber();
    $this->writeBuildNumber();
    $this->setProjectBuildNumberProperty();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Setter for XML attribute buildNumberProperty.
   *
   * @param string $buildNumberProperty Name of variable holding the build number.
   */
  public function setBuildNumberProperty(string $buildNumberProperty): void
  {
    $this->buildNumberProperty = $buildNumberProperty;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Setter for XML attribute file.
   *
   * @param string $filename The name of the file containing the build number.
   */
  public function setFile(string $filename): void
  {
    $this->filename = $filename;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Bumps the build number.
   */
  private function bumpBuildNumber(): void
  {
    if ($this->buildNumber!==null)
    {
      $this->buildNumber++;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Reads the build version from the file.
   */
  private function readBuildNumber(): void
  {
    if ($this->filename!==null)
    {
      if (is_file($this->filename))
      {
        $content = file_get_contents($this->filename);
        if ($content===false)
        {
          $this->logError('Not readable file %s.', $this->filename);
        }

        $valid = $this->validateBuildNumber($content);
        if ($valid)
        {
          $this->logInfo('Current build number is %d.', $this->buildNumber);
        }
        else
        {
          $this->logError("Not a valid build number: '%s'.", $content);
        }
      }
      else
      {
        $this->logInfo('File %s does not exist.', $this->filename);
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Adds a new property to phing project holding the build number.
   */
  private function setProjectBuildNumberProperty(): void
  {
    if ($this->buildNumberProperty!==null && $this->buildNumber!==null)
    {
      $this->project->setProperty($this->buildNumberProperty, (string)$this->buildNumber);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Validates whether a string is a valid build number.
   *
   * @param string $buildNumber The string to be validated.
   */
  private function validateBuildNumber(string $buildNumber): bool
  {
    $n = preg_match('/^(\d+)$/', $buildNumber, $matches);
    if ($n===1)
    {
      $this->buildNumber = (int)$matches[1];

      return true;
    }

    return false;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Writes the build number to file.
   */
  private function writeBuildNumber(): void
  {
    if ($this->filename && $this->buildNumber!==null)
    {
      $this->logInfo('New build number is %d.', $this->buildNumber);
      $status = file_put_contents($this->filename, $this->buildNumber);
      if (!$status)
      {
        $this->logError('File %s is not writable.', $this->filename);
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
