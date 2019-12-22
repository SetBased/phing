<?php
declare(strict_types=1);

/**
 * Phing task for reading a Semantic Version from the standard input.
 */
class ReadSemanticVersionTask extends SetBasedTask
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The filename with contain semantic version number.
   *
   * @var string
   */
  private $filename;

  /**
   * Name of variable in a build for major part of version number.
   *
   * @var string
   */
  private $majorProperty;

  /**
   * Name of variable in a build for minor part of version number.
   *
   * @var string
   */
  private $minorProperty;

  /**
   * Array with parts of new version number.
   *
   * @var array
   */
  private $newVersion = [];

  /**
   * Name of variable in a build for patch part of version number.
   *
   * @var string
   */
  private $patchVersion;

  /**
   * Name of variable in a build for pre-release part of version number (i.e. the part after - (if any)).
   *
   * @var string
   */
  private $preReleaseVersion;

  /**
   * Array with parts of previous version number.
   *
   * @var array
   */
  private $previousVersion = [];

  /**
   * Name of variable in a build for release part of version number (i.e. MAJOR.MINOR.PATCH).
   *
   * @var string
   */
  private $releaseVersion;

  /**
   * Name of variable in a build for full version number.
   *
   * @var string
   */
  private $versionProperty;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Main method of this task.
   */
  public function main()
  {
    // Read current version form file.
    $this->readPreviousVersionNumber();

    // Set new version from CLI.
    $this->setNewVersionNumber();

    // Update version in file.
    $this->updateVersionInFile();

    // Set version properties for project.
    $this->setProjectVersionProperties();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Setter for XML attribute file.
   *
   * @param string $filename The filename with contain semantic version number.
   */
  public function setFile(string $filename): void
  {
    $this->filename = $filename;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Setter for XML attribute majorProperty.
   *
   * @param string $majorVersion Name of variable in a build for major part of version number.
   */
  public function setMajorProperty(string $majorVersion): void
  {
    $this->majorProperty = $majorVersion;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Setter for XML attribute minorProperty.
   *
   * @param string $minorVersion Name of variable in a build for minor part of version number.
   */
  public function setMinorProperty(string $minorVersion): void
  {
    $this->minorProperty = $minorVersion;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Setter for XML attribute patchProperty.
   *
   * @param string $patchVersion Name of variable in a build for patch part of version number.
   */
  public function setPatchProperty(string $patchVersion): void
  {
    $this->patchVersion = $patchVersion;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Setter for XML attribute preReleaseProperty.
   *
   * @param string $preReleaseVersion Name of variable in a build for pre-release part of version number (i.e. the part
   *                                  after - (if any)).
   */
  public function setPreReleaseProperty(string $preReleaseVersion): void
  {
    $this->preReleaseVersion = $preReleaseVersion;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Setter for XML attribute releaseProperty.
   *
   * @param string $releaseVersion Name of variable in a build for release part of version number (i.e.
   *                               MAJOR.MINOR.PATCH).
   */
  public function setReleaseProperty(string $releaseVersion): void
  {
    $this->releaseVersion = $releaseVersion;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Setter for XML attribute versionProperty.
   *
   * @param string $versionProperty Name of variable in a build for full version number.
   */
  public function setVersionProperty(string $versionProperty): void
  {
    $this->versionProperty = $versionProperty;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Reads previous version from file if filename is set.
   */
  private function readPreviousVersionNumber(): void
  {
    if ($this->filename)
    {
      if (is_file($this->filename))
      {
        $content = file_get_contents($this->filename);
        if ($content===false)
        {
          $this->logError("Not readable file %s.", $this->filename);
        }

        if ($content)
        {
          $this->previousVersion = $this->validateSemanticVersion($content);
          if ($this->previousVersion)
          {
            $this->logInfo("Current version is %s", $this->previousVersion['version']);
          }
          else
          {
            $this->logError("Version is %s is not a valid Semantic Version", $this->previousVersion['version']);
          }
        }
      }
      else
      {
        $this->logInfo("File %s does not exist", $this->filename);
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Read new version number from php://stdin stream i.e CLI.
   */
  private function setNewVersionNumber(): void
  {
    $valid = false;
    while (!$valid)
    {
      echo "Enter new Semantic Version: ";

      $line             = fgets(STDIN);
      $this->newVersion = $this->validateSemanticVersion($line);
      $valid            = ($this->newVersion);

      if (!$valid)
      {
        $this->logInfo("%s is not a valid Semantic Version", trim($line, "\n"));
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Adds new properties to phing project with data about version number.
   */
  private function setProjectVersionProperties(): void
  {
    if ($this->versionProperty)
    {
      $this->project->setProperty($this->versionProperty, $this->newVersion['version']);
    }

    if ($this->releaseVersion)
    {
      $this->project->setProperty($this->releaseVersion, $this->newVersion['release']);
    }
    if ($this->preReleaseVersion)
    {
      $this->project->setProperty($this->preReleaseVersion, $this->newVersion['pre-release']);
    }

    if ($this->majorProperty)
    {
      $this->project->setProperty($this->majorProperty, $this->newVersion['major']);
    }
    if ($this->minorProperty)
    {
      $this->project->setProperty($this->minorProperty, $this->newVersion['minor']);
    }
    if ($this->patchVersion)
    {
      $this->project->setProperty($this->patchVersion, $this->newVersion['patch']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Writes new version number into file if the filename is set.
   */
  private function updateVersionInFile(): void
  {
    if ($this->filename)
    {
      $status = file_put_contents($this->filename, $this->newVersion['version']);
      if (!$status)
      {
        $this->logError("File %s is not writable", $this->filename);
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Validates a string is a valid Semantic Version. If the string is semantic version returns an array with the parts
   * of the semantic version. Otherwise returns null.
   *
   * @param string $version The string the be validated.
   *
   * @return array
   */
  private function validateSemanticVersion(string $version): array
  {
    /**
     * Notice:
     * Version validation http://semver.org/
     * Example:
     * Valid version numbers: 1, 1.2, 2.2.3, 1.2.6-alpha, 4.2.3-alpha.beta, 1.5.0-rc.1;
     * Invalid version numbers: 1., 1.2., 1beta, 4.5alpha, 1.2.3-rc_1;
     */
    $status = preg_match('/^(\d+)(?:\.(\d+))?(?:\.((\d+)(?:-([A-Za-z]+)(?:\.(\w+))?)?))?$/', $version, $matches);

    $parts = [];
    if ($status)
    {
      $parts['version'] = $matches[0];
      $parts['major']   = $matches[1];
      $parts['minor']   = $matches[2];

      // The above regexp will put the pre-release part in the patch part. Separate patch and pre-release part using
      // ordinary string manipulation.
      $pos = strpos($matches[3], '-');
      if ($pos!==false)
      {
        $parts['patch']       = substr($matches[3], 0, $pos);
        $parts['pre-release'] = substr($matches[3], $pos + 1);
      }
      else
      {
        $parts['patch']       = $matches[3];
        $parts['pre-release'] = '';
      }

      $parts['release'] = $parts['major'].'.'.$parts['minor'].'.'.$parts['patch'];
    }

    return $parts;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
