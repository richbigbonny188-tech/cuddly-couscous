<?php
/* --------------------------------------------------------------
   FilesystemManager.inc.php 2019-07-25
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class FilesystemManager
 */
class FilesystemManager
{
	/**
	 * @param array $moveArray array of array('old' => 'source_path', 'new' => 'new_path') arrays
	 * @param bool  $debug
	 */
	static public function move(array $moveArray, $debug = false)
	{
		$verbose = $debug ? '-v ' : '';
		$shopDir = dirname(dirname(__DIR__)) . '/';
		
		foreach($moveArray as $filePaths)
		{
			if($filePaths['old'] === '' || $filePaths['new'] === '' || strpos($filePaths['old'], '..') !== false
			   || strpos($filePaths['new'], '..') !== false
			)
			{
				continue;
			}
			
			$source = $shopDir . $filePaths['old'];
			$target = $shopDir . $filePaths['new'];
			
			if(!file_exists($target))
			{
				CLIHelper::doSystem('mv ' . $verbose . $source . ' ' . $target);
			}
			elseif(is_dir($target))
			{
				CLIHelper::doSystem('rsync -a ' . $verbose . ' --ignore-existing ' . $source . '/* ' . $target);
				
				$iterator = new RecursiveDirectoryIterator($source);
				
				foreach(new RecursiveIteratorIterator($iterator) as $sourceFile)
				{
					if(substr($sourceFile, -1) === '.')
					{
						continue;
					}
					
					$targetFile = $target . substr($sourceFile, strlen($source));
					
					if(is_file($sourceFile) && md5_file($sourceFile) === md5_file($targetFile))
					{
						CLIHelper::doSystem('rm -f ' . $verbose . $sourceFile);
					}
				}
				
				CLIHelper::doSystem('find ' . $source . ' -type d -empty -delete');
			}
		}
	}
	
	
	/**
	 * @param array $deleteFilePaths
	 * @param bool  $debug
	 */
	static public function delete(array $deleteFilePaths, $debug = false)
	{
		$verbose = $debug ? '-v ' : '';
		$shopDir = dirname(dirname(__DIR__));
		
		foreach($deleteFilePaths as $filePath)
		{
			$filePath = trim($filePath);
			if($filePath === '' || strpos($filePath, '..') !== false)
			{
				continue;
			}
			
			$filePath = $shopDir . '/' . $filePath;
			
			if(substr($filePath, -1) === '/')
			{
				$filePath = substr($filePath, 0, -1);
			}
			
			if(substr($filePath, -2) === '/*')
			{
				$filePath = substr($filePath, 0, -2);
				
				if(file_exists($filePath))
				{
					CLIHelper::doSystem('rm -Rf ' . $verbose . $filePath);
				}
			}
			elseif(file_exists($filePath))
			{
				if(is_dir($filePath))
				{
					CLIHelper::doSystem('rmdir ' . $verbose . $filePath);
				}
				else
				{
					CLIHelper::doSystem('rm -f ' . $verbose . $filePath);
				}
			}
		}
	}
	
	
	/**
	 * @param string $chmodFilePath
	 * @param bool   $debug
	 */
	static public function singleChmod($chmodFilePath, $debug = false)
	{
		$verbose   = $debug ? '-v' : '';
		$filePaths = file($chmodFilePath);
		$shopDir   = dirname(dirname(__DIR__));
		
		foreach($filePaths as $filePath)
		{
			$filePath = trim($filePath);
			if($filePath === '' || strpos($filePath, '..') !== false)
			{
				continue;
			}
			
			$filePath = $shopDir . $filePath;
			
			if(substr($filePath, -1) === '/')
			{
				$filePath = substr($filePath, 0, -1);
			}
			
			if(file_exists($filePath) && !is_writable($filePath))
			{
				CLIHelper::doSystem('chmod ' . $verbose . ' 0777 ' . $filePath);
			}
		}
	}
	
	
	/**
	 * @param string $chmodFilePath
	 * @param array  $completeChmodPaths
	 * @param bool   $debug
	 */
	static public function recursiveChmod($chmodFilePath,
	                                      array $completeChmodPaths = array('admin/includes/magnalister'),
	                                      $debug = false)
	{
		$verbose   = $debug ? '-v ' : ' ';
		$filePaths = file($chmodFilePath);
		$shopDir   = dirname(dirname(__DIR__));
		
		foreach($filePaths as $filePath)
		{
			$includeFiles = false;
			
			$filePath = trim($filePath);
			if($filePath === '' || strpos($filePath, '..') !== false)
			{
				continue;
			}
			
			foreach($completeChmodPaths as $path)
			{
				if(strpos($filePath, $path) === 1)
				{
					$includeFiles = true;
					break;
				}
			}
			
			$filePath = $shopDir . $filePath;
			
			if(substr($filePath, -1) === '/')
			{
				$filePath = substr($filePath, 0, -1);
			}
			
			if(file_exists($filePath) && !is_writable($filePath))
			{
				if($includeFiles)
				{
					CLIHelper::doSystem('chmod -R ' . $verbose . ' 0777 ' . $filePath);
				}
				else
				{
					CLIHelper::doSystem('find ' . $filePath . ' -type d -print0 | xargs -0 chmod ' . $verbose . '777');
				}
			}
		}
	}
}
