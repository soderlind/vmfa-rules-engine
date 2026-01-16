<?php
/**
 * Tests for GitHubPluginUpdater class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Update;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Update\GitHubPluginUpdater;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Mockery;

/**
 * GitHubPluginUpdater test case.
 */
class GitHubPluginUpdaterTest extends TestCase {

	/**
	 * Test that constructor throws exception for missing github_url.
	 */
	public function test_constructor_throws_for_missing_github_url(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( "Required parameter 'github_url' is missing or empty." );

		new GitHubPluginUpdater( [
			'plugin_file' => '/path/to/plugin.php',
			'plugin_slug' => 'test-plugin',
		] );
	}

	/**
	 * Test that constructor throws exception for missing plugin_file.
	 */
	public function test_constructor_throws_for_missing_plugin_file(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( "Required parameter 'plugin_file' is missing or empty." );

		new GitHubPluginUpdater( [
			'github_url'  => 'https://github.com/test/repo',
			'plugin_slug' => 'test-plugin',
		] );
	}

	/**
	 * Test that constructor throws exception for missing plugin_slug.
	 */
	public function test_constructor_throws_for_missing_plugin_slug(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( "Required parameter 'plugin_slug' is missing or empty." );

		new GitHubPluginUpdater( [
			'github_url'  => 'https://github.com/test/repo',
			'plugin_file' => '/path/to/plugin.php',
		] );
	}

	/**
	 * Test that constructor throws exception for empty github_url.
	 */
	public function test_constructor_throws_for_empty_github_url(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( "Required parameter 'github_url' is missing or empty." );

		new GitHubPluginUpdater( [
			'github_url'  => '',
			'plugin_file' => '/path/to/plugin.php',
			'plugin_slug' => 'test-plugin',
		] );
	}

	/**
	 * Test that constructor registers init action.
	 */
	public function test_constructor_registers_init_action(): void {
		Actions\expectAdded( 'init' )
			->once()
			->with( Mockery::type( 'array' ), 10, 1 );

		new GitHubPluginUpdater( [
			'github_url'  => 'https://github.com/test/repo',
			'plugin_file' => '/path/to/plugin.php',
			'plugin_slug' => 'test-plugin',
		] );
	}

	/**
	 * Test that constructor uses default branch when not provided.
	 */
	public function test_constructor_uses_default_branch(): void {
		Actions\expectAdded( 'init' )->once();

		$updater = new GitHubPluginUpdater( [
			'github_url'  => 'https://github.com/test/repo',
			'plugin_file' => '/path/to/plugin.php',
			'plugin_slug' => 'test-plugin',
		] );

		$this->assertInstanceOf( GitHubPluginUpdater::class, $updater );
	}

	/**
	 * Test that constructor accepts custom branch.
	 */
	public function test_constructor_accepts_custom_branch(): void {
		Actions\expectAdded( 'init' )->once();

		$updater = new GitHubPluginUpdater( [
			'github_url'  => 'https://github.com/test/repo',
			'plugin_file' => '/path/to/plugin.php',
			'plugin_slug' => 'test-plugin',
			'branch'      => 'develop',
		] );

		$this->assertInstanceOf( GitHubPluginUpdater::class, $updater );
	}

	/**
	 * Test that constructor enables release assets when name_regex is provided.
	 */
	public function test_constructor_enables_release_assets_with_name_regex(): void {
		Actions\expectAdded( 'init' )->once();

		$updater = new GitHubPluginUpdater( [
			'github_url'  => 'https://github.com/test/repo',
			'plugin_file' => '/path/to/plugin.php',
			'plugin_slug' => 'test-plugin',
			'name_regex'  => '/test-plugin\.zip/',
		] );

		$this->assertInstanceOf( GitHubPluginUpdater::class, $updater );
	}

	/**
	 * Test that constructor respects explicit enable_release_assets setting.
	 */
	public function test_constructor_respects_explicit_release_assets_setting(): void {
		Actions\expectAdded( 'init' )->once();

		$updater = new GitHubPluginUpdater( [
			'github_url'            => 'https://github.com/test/repo',
			'plugin_file'           => '/path/to/plugin.php',
			'plugin_slug'           => 'test-plugin',
			'name_regex'            => '/test-plugin\.zip/',
			'enable_release_assets' => false,
		] );

		$this->assertInstanceOf( GitHubPluginUpdater::class, $updater );
	}

	/**
	 * Test static create method.
	 */
	public function test_create_static_method(): void {
		Actions\expectAdded( 'init' )->once();

		$updater = GitHubPluginUpdater::create(
			'https://github.com/test/repo',
			'/path/to/plugin.php',
			'test-plugin'
		);

		$this->assertInstanceOf( GitHubPluginUpdater::class, $updater );
	}

	/**
	 * Test static create method with custom branch.
	 */
	public function test_create_static_method_with_branch(): void {
		Actions\expectAdded( 'init' )->once();

		$updater = GitHubPluginUpdater::create(
			'https://github.com/test/repo',
			'/path/to/plugin.php',
			'test-plugin',
			'develop'
		);

		$this->assertInstanceOf( GitHubPluginUpdater::class, $updater );
	}

	/**
	 * Test static create_with_assets method.
	 */
	public function test_create_with_assets_static_method(): void {
		Actions\expectAdded( 'init' )->once();

		$updater = GitHubPluginUpdater::create_with_assets(
			'https://github.com/test/repo',
			'/path/to/plugin.php',
			'test-plugin',
			'/test-plugin\.zip/'
		);

		$this->assertInstanceOf( GitHubPluginUpdater::class, $updater );
	}

	/**
	 * Test static create_with_assets method with custom branch.
	 */
	public function test_create_with_assets_static_method_with_branch(): void {
		Actions\expectAdded( 'init' )->once();

		$updater = GitHubPluginUpdater::create_with_assets(
			'https://github.com/test/repo',
			'/path/to/plugin.php',
			'test-plugin',
			'/test-plugin\.zip/',
			'release'
		);

		$this->assertInstanceOf( GitHubPluginUpdater::class, $updater );
	}

	/**
	 * Test that setup_updater is callable.
	 *
	 * Note: We cannot fully test setup_updater as it requires WordPress
	 * constants (WP_PLUGIN_DIR) and the PucFactory library.
	 * Integration tests would be needed for full coverage.
	 */
	public function test_setup_updater_is_callable(): void {
		Actions\expectAdded( 'init' )->once();

		$updater = new GitHubPluginUpdater( [
			'github_url'  => 'https://github.com/test/repo',
			'plugin_file' => '/path/to/plugin.php',
			'plugin_slug' => 'test-plugin',
		] );

		// Verify setup_updater method exists and is callable.
		$this->assertTrue( method_exists( $updater, 'setup_updater' ) );
		$this->assertTrue( is_callable( [ $updater, 'setup_updater' ] ) );
	}

	/**
	 * Test that all required parameters together work.
	 */
	public function test_full_configuration(): void {
		Actions\expectAdded( 'init' )->once();

		$updater = new GitHubPluginUpdater( [
			'github_url'            => 'https://github.com/soderlind/vmfa-rules-engine',
			'plugin_file'           => '/var/www/html/wp-content/plugins/vmfa-rules-engine/vmfa-rules-engine.php',
			'plugin_slug'           => 'vmfa-rules-engine',
			'branch'                => 'main',
			'name_regex'            => '/vmfa-rules-engine\.zip/',
			'enable_release_assets' => true,
		] );

		$this->assertInstanceOf( GitHubPluginUpdater::class, $updater );
	}
}
