<?php

namespace Tadasei\BackendTrashableNotifications\Console;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

use Symfony\Component\Process\{PhpExecutableFinder, Process};

class InstallCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = "trashable-notifications:install";
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Publishes trashable notifications management utilities";

	/**
	 * Execute the console command.
	 *
	 * @return int|null
	 */
	public function handle()
	{
		$supervisorConfigPaths = collect([]);

		// Publish the scaffolding files

		$this->publishDirectory(__DIR__ . "/../../stubs", function (
			string $path
		) use ($supervisorConfigPaths) {
			if (!$this->isSupervisorConfigPath($path)) {
				return $path;
			}

			[$fileName] = str($path)
				->scan("supervisor_conf/%s")
				->all();

			$targetPath = str_replace(
				$fileName,
				str(config("app.name"))
					->lower()
					->snake() . "_$fileName",
				$path
			);

			$supervisorConfigPaths->push($targetPath);

			return $targetPath;
		});

		// Update supervisord config files content with app name

		$supervisorConfigPaths->each(
			fn(string $path) => $this->replaceInFile(
				"stub",
				str(config("app.name"))
					->lower()
					->snake(),
				$path
			)
		);

		// Notify of completion

		$this->components->info("Scaffolding complete.");

		return 0;
	}

	protected function isSupervisorConfigPath(string $path): bool
	{
		return str_contains($path, "supervisor_conf");
	}

	protected function publishDirectory(
		string $directory,
		?Closure $getTargetFilePath = null,
		?string $prefix = null
	): void {
		$files = $this->listDirectoryFiles(
			$directory,
			$getTargetFilePath,
			$prefix
		);

		// Ensuring target directories exist

		$this->ensureTargetDirectoriesExist($files);

		// Copying files

		$this->copyFiles($files);
	}

	protected function copyFiles(array $files): void
	{
		collect($files)->each(function (array $file) {
			if (!file_exists($file["target"])) {
				copy($file["source"], $file["target"]);
			}
		});
	}

	protected function ensureTargetDirectoriesExist(array $files): void
	{
		collect($files)
			->map(
				fn(array $file) => str_replace(
					"/{$file["name"]}",
					"",
					$file["target"]
				)
			)
			->unique()
			->each(function (string $targetDirectory) {
				if (!file_exists($targetDirectory)) {
					mkdir($targetDirectory, recursive: true);
				}
			});
	}

	protected function listDirectoryFiles(
		string $directory,
		?Closure $getTargetFilePath = null,
		?string $prefix = null
	): array {
		$directoryMap = $this->getDirectoryMap(
			$directory,
			$getTargetFilePath,
			$prefix
		);

		return $this->getDirectoryMapFiles($directoryMap);
	}

	protected function getDirectoryMapFiles(array $directoryMap): array
	{
		return collect($directoryMap)
			->flatMap(
				fn(array $item) => key_exists("map", $item)
					? $this->getDirectoryMapFiles($item["map"])
					: [$item]
			)
			->all();
	}

	protected function getDirectoryMap(
		string $directory,
		?Closure $getTargetFilePath = null,
		?string $prefix = null
	): array {
		$prefix ??= "$directory/";

		$getTargetFilePath ??= fn(string $path): string => $path;

		return collect(scandir($directory))
			->reject(fn(string $name) => in_array($name, [".", ".."]))
			->values()
			->map(function (string $name) use (
				$directory,
				$getTargetFilePath,
				$prefix
			) {
				$source = "$directory/$name";

				return [
					"name" => $name,
					"source" => $source,
					...is_dir($source)
						? [
							"map" => $this->getDirectoryMap(
								$source,
								$getTargetFilePath,
								$prefix
							),
						]
						: [
							"target" => $getTargetFilePath(
								base_path(
									str_replace($prefix, "", $directory) .
										"/$name"
								)
							),
						],
				];
			})
			->all();
	}

	/**
	 * Install the middleware to a group in the application Http Kernel.
	 *
	 * @param  string  $after
	 * @param  string  $name
	 * @param  string  $group
	 * @return void
	 */
	protected function installMiddlewareAfter($after, $name, $group = "web")
	{
		$httpKernel = file_get_contents(app_path("Http/Kernel.php"));

		$middlewareGroups = Str::before(
			Str::after($httpKernel, '$middlewareGroups = ['),
			"];"
		);
		$middlewareGroup = Str::before(
			Str::after($middlewareGroups, "'$group' => ["),
			"],"
		);

		if (!Str::contains($middlewareGroup, $name)) {
			$modifiedMiddlewareGroup = str_replace(
				$after . ",",
				$after . "," . PHP_EOL . "            " . $name . ",",
				$middlewareGroup
			);

			file_put_contents(
				app_path("Http/Kernel.php"),
				str_replace(
					$middlewareGroups,
					str_replace(
						$middlewareGroup,
						$modifiedMiddlewareGroup,
						$middlewareGroups
					),
					$httpKernel
				)
			);
		}
	}

	/**
	 * Installs the given Composer Packages into the application.
	 *
	 * @param  array  $packages
	 * @param  bool  $asDev
	 * @return bool
	 */
	protected function requireComposerPackages(array $packages, $asDev = false)
	{
		$composer = $this->option("composer");

		if ($composer !== "global") {
			$command = ["php", $composer, "require"];
		}

		$command = array_merge(
			$command ?? ["composer", "require"],
			$packages,
			$asDev ? ["--dev"] : []
		);

		return (new Process($command, base_path(), [
			"COMPOSER_MEMORY_LIMIT" => "-1",
		]))
			->setTimeout(null)
			->run(function ($type, $output) {
				$this->output->write($output);
			}) === 0;
	}

	/**
	 * Removes the given Composer Packages from the application.
	 *
	 * @param  array  $packages
	 * @param  bool  $asDev
	 * @return bool
	 */
	protected function removeComposerPackages(array $packages, $asDev = false)
	{
		$composer = $this->option("composer");

		if ($composer !== "global") {
			$command = ["php", $composer, "remove"];
		}

		$command = array_merge(
			$command ?? ["composer", "remove"],
			$packages,
			$asDev ? ["--dev"] : []
		);

		return (new Process($command, base_path(), [
			"COMPOSER_MEMORY_LIMIT" => "-1",
		]))
			->setTimeout(null)
			->run(function ($type, $output) {
				$this->output->write($output);
			}) === 0;
	}

	/**
	 * Update the "package.json" file.
	 *
	 * @param  callable  $callback
	 * @param  bool  $dev
	 * @return void
	 */
	protected static function updateNodePackages(
		callable $callback,
		$dev = true
	) {
		if (!file_exists(base_path("package.json"))) {
			return;
		}

		$configurationKey = $dev ? "devDependencies" : "dependencies";

		$packages = json_decode(
			file_get_contents(base_path("package.json")),
			true
		);

		$packages[$configurationKey] = $callback(
			array_key_exists($configurationKey, $packages)
				? $packages[$configurationKey]
				: [],
			$configurationKey
		);

		ksort($packages[$configurationKey]);

		file_put_contents(
			base_path("package.json"),
			json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
				PHP_EOL
		);
	}

	/**
	 * Delete the "node_modules" directory and remove the associated lock files.
	 *
	 * @return void
	 */
	protected static function flushNodeModules()
	{
		tap(new Filesystem(), function ($files) {
			$files->deleteDirectory(base_path("node_modules"));

			$files->delete(base_path("yarn.lock"));
			$files->delete(base_path("package-lock.json"));
		});
	}

	/**
	 * Replace a given string within a given file.
	 *
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $path
	 * @return void
	 */
	protected function replaceInFile($search, $replace, $path)
	{
		file_put_contents(
			$path,
			str_replace($search, $replace, file_get_contents($path))
		);
	}

	/**
	 * Get the path to the appropriate PHP binary.
	 *
	 * @return string
	 */
	protected function phpBinary()
	{
		return (new PhpExecutableFinder())->find(false) ?: "php";
	}

	/**
	 * Run the given commands.
	 *
	 * @param  array  $commands
	 * @return void
	 */
	protected function runCommands($commands)
	{
		$process = Process::fromShellCommandline(
			implode(" && ", $commands),
			null,
			null,
			null,
			null
		);

		if (
			"\\" !== DIRECTORY_SEPARATOR &&
			file_exists("/dev/tty") &&
			is_readable("/dev/tty")
		) {
			try {
				$process->setTty(true);
			} catch (RuntimeException $e) {
				$this->output->writeln(
					"  <bg=yellow;fg=black> WARN </> " .
						$e->getMessage() .
						PHP_EOL
				);
			}
		}

		$process->run(function ($type, $line) {
			$this->output->write("    " . $line);
		});
	}
}
