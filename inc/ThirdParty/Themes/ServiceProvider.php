<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\DelayJS\ServiceProvider as DelayJSServiceProvider;
class ServiceProvider extends AbstractServiceProvider {

	protected $simple_registration_classes = [
		Avada::class => true,
		Bridge::class => true,
		Flatsome::class => false,
		Jevelin::class => false,
		MinimalistBlogger::class => false,
		Polygon::class => false,
		Uncode::class => false,
		Xstore::class => false,
	];

	public function get_common_subscribers(): array
	{

		$simple_registration_classes_ids = array_map(function ($class) {
			return $this->generate_id( $class, true );
		}, array_keys($this->simple_registration_classes));

		$subscribers = array_merge($simple_registration_classes_ids, [
			'divi',
		]);

		return array_map(function ($id) {
			return $this->generate_container_id( $id );
		}, $subscribers);
	}

	public function declare()
	{
		foreach ($this->simple_registration_classes as $simple_registration_class => $has_options) {
			$id = $this->generate_id($simple_registration_class, true);
			$this->register_service($id, function (string $id) use ($simple_registration_class, $has_options) {
				if(! $has_options ) {
					$this->share($id, $simple_registration_class )
						->addTag( 'common_subscriber' );
				}
				$this
					->share( $id, $simple_registration_class )
					->addArgument( $this->get_external('options') )
					->addTag( 'common_subscriber' );
			});
		}

		$this->register_service('divi' , function ($id) {
			$this
				->share( $id, Divi::class )
				->addArgument( $this->get_external( 'options_api' ) )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_external( 'delay_js_html', DelayJSServiceProvider::class ) )
				->addTag( 'common_subscriber' );
		});
	}
}
