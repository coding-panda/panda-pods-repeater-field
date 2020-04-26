<?php
/**
 * PPRF Elementor Accordion Widget
 *
 * Elementor widget that inserts an accordion element to page
 *
 * @since 1.4.6
 */
 
namespace Elementor;
class PPRF_Elementor_Accordion_Widget extends Widget_Base {

	public function get_name() {
		return 'pprf_elementor_accordion';
	}
	
	public function get_title() {
		return 'PPRF Accordion';
	}
	
	public function get_icon() {
		return 'fab fa-wordpress';
	}
	
	public function get_categories() {
		return [ 'wordpress' ];
	}
	
	protected function _register_controls() {

		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'Content', 'elementor' ),
			]
		);
		
		$this->add_control(
			'title',
			[
				'label' => __( 'Title', 'elementor' ),
				'label_block' => true,
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter your title', 'elementor' ),
			]
		);

		$this->add_control(
			'subtitle',
			[
				'label' => __( 'Sub-title', 'elementor' ),
				'label_block' => true,
				'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Enter your sub-title', 'elementor' ),
			]
		);

		$this->add_control(
			'link',
			[
				'label' => __( 'Link', 'elementor' ),
				'type' => Controls_Manager::URL,
				'placeholder' => __( 'https://your-link.com', 'elementor' ),
				'default' => [
					'url' => '',
				]
			]
		);

		$this->end_controls_section();
	}
	
	protected function render() {

        $settings = $this->get_settings_for_display();
        $url = $settings['link']['url'];
		echo  "<a href='$url'><div class='title'>$settings[title]</div> <div class='subtitle'>$settings[subtitle]</div></a>";
		 

	}
	
	protected function _content_template() {

    }
	
	
}