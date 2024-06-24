# Phing Extensions

[![Join the chat at https://gitter.im/SetBased/phing-extensions](https://badges.gitter.im/SetBased/phing-extensions.svg)](https://gitter.im/SetBased/phing-extensions?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
A set of Phing extensions.

[![Gitter](https://badges.gitter.im/SetBased/phing-extensions.svg)](https://gitter.im/SetBased/phing-extensions?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
[![License](https://poser.pugx.org/setbased/phing-extensions/license)](https://packagist.org/packages/setbased/phing-extensions)
[![Latest Stable Version](https://poser.pugx.org/setbased/phing-extensions/v/stable)](https://packagist.org/packages/setbased/phing-extensions)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SetBased/phing-extensions/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SetBased/phing-extensions/?branch=master)

# Overview
This collection of Phing extensions provides:

  * LastCommitTimeTask
  * ReadSemanticVersionTask
  * RemoveEmptyDirectoriesTask
  * SetDirectoryMTimeTask
     
## LastCommitTimeTask
LastCommitTimeTask sets the modification time of files to the last commit time in Git. 
       
#### Parameters
| Name        | Type   | Description                                                                            | Default | Required |
| ------------| ------ | -------------------------------------------------------------------------------------- | ------- | -------- |
| Dir         | string | The directory were under which the sources are located. Typically the build directory. |         | Yes      |     
| HaltOnError | bool   | If true the build fails on errors, otherwise this task generates error events.         | True    | No       |                  
                             
#### Example
```XML
<taskdef name="LastCommitTimeTask" classname="vendor.setbased.phing-extensions.src.Task.LastCommitTimeTask"/>
<LastCommitTimeTask Dir="build"/>
```

## ReadSemanticVersionTask
ReadSemanticVersionTask asks the user for [Semantic version](http://semver.org/) and validates 
the given input is a valid [Semantic version](http://semver.org/). The version and its parts are published
under supplied properties for further usage in the Phing build script. 

#### Parameters
| Name               | Type   | Description                                                                       | Default | Required |
| ------------------ | ------ | --------------------------------------------------------------------------------- | ------- | -------- |
| File               | string | Filename for storing the entered SemanticVersion.                                 |         | No       |     
| HaltOnError        | bool   | If true the build fails on errors, otherwise this task generates error events.    | True    | No       |                  
| VersionProperty    | string | The name of the property for publishing the version (e.g. 1.2.3-alpha.1).         |         | No       |                            
| ReleaseProperty    | string | The name of the property for publishing the major, minor, and patch version (e.g. 1.2.3). |         | No       |                            
| MajorProperty      | string | The name of the property for publishing the major version (e.g. 1).               |         | No       |                          
| MinorProperty      | string | The name of the property for publishing the minor version (e.g. 2).               |         | No       |                          
| PatchProperty      | string | The name of the property for publishing the patch version (e.g. 3).               |         | No       |                          
| PreReleaseProperty | string | The name of the property for publishing the pre-release version (e.g. alpha.1).   |         | No       |                               
#### Example
```XML
<taskdef name="ReadSemanticVersion" classname="vendor.setbased.phing-extensions.src.Task.ReadSemanticVersionTask"/>
<ReadSemanticVersion File=".version"  VersionProperty="VERSION"/>
<echo message="${VERSION}"/>
```

## RemoveEmptyDirectoriesTask
RemoveEmptyDirectoriesTask removes recursively empty directories under a parent directory. 

#### Parameters
| Name         | Type   | Description                                                                    | Default | Required |
| ------------ | ------ | ------------------------------------------------------------------------------ | ------- | -------- |
| Dir          | string | The parent directory under which empty directories must be removed.            |         | True     |
| RemoveParent | bool   | If true the parent directory is removed as well if it is empty.                | False   | No       |    
| HaltOnError  | bool   | If true the build fails on errors, otherwise this task generates error events. | True    | No       |                  

#### Example
```XML
<taskdef name="RemoveEmptyDirs" classname="vendor.setbased.phing-extensions.src.Task.RemoveEmptyDirsTask"/>
<RemoveEmptyDirectoriesTask Dir="build/www/js"  RemoveParent="false"/>
```

## SetDirectoryMTimeTask
SetDirectoryMTimeTask sets recursively the modification time of directories to the maximum modification time of its 
entries.

#### Parameters
| Name         | Type   | Description                                                                    | Default | Required |
| ------------ | ------ | ------------------------------------------------------------------------------ | ------- | -------- |
| Dir          | string | The parent directory.                                                          |         | True     |
| HaltOnError  | bool   | If true the build fails on errors, otherwise this task generates error events. | True    | No       |                  

#### Example
```XML
<taskdef name="SetDirectoryMTimeTask" classname="vendor.setbased.phing-extensions.src.Task.SetDirectoryMTimeTask"/>
<SetDirectoryMTimeTask Dir="build"/>
```

# Installation
We recommend to install setbased/phing via [Composer](https://getcomposer.org/):

```json
{
  "require-dev": {
    "setbased/phing-extensions": "2.*"
  }
}
```
