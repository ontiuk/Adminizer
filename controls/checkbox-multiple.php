<?php

/**
 * Multiple checkbox customize control class.
 *
 * @package WordPress
 */
class WP_Customize_Control_Checkbox_Multiple extends WP_Customize_Control {

    /**
     * The type of customize control being rendered.
     *
     * @var    string $type
     * @access public
     */
    public $type = 'checkbox-multiple';

    /**
     * Enqueue scripts/styles.
     *
     * @access public
     */
    public function enqueue() {
        wp_enqueue_script( 'customiser-checkbox-multiple', trailingslashit( get_template_directory_uri() ) . 'inc/controls/js/checkbox-multiple.js', array( 'jquery' ) );
    }

    /**
     * Displays the control content.
     *
     * @access public
     */
    public function render_content() {

        // No options?
        if ( empty( $this->choices ) ) { return; }
?>

        <?php if ( !empty( $this->label ) ) : ?>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
        <?php endif; ?>

        <?php if ( !empty( $this->description ) ) : ?>
            <span class="description customize-control-description"><?php echo $this->description; ?></span>
        <?php endif; ?>

        <?php $multi_values = !is_array( $this->value() ) ? explode( ',', $this->value() ) : $this->value(); ?>

        <ul>
            <?php foreach ( $this->choices as $value => $label ) : ?>

                <li>
                    <label>
                        <input type="checkbox" name="<?php echo $this->type . '-' . $value; ?>" id="<?php echo $this->type . '_' . $value; ?>" class="<?php echo $this->type . '-checkbox'; ?>" value="<?php echo esc_attr( $value ); ?>" <?php checked( in_array( $value, $multi_values ) ); ?> /> 
                        <?php echo esc_html( $label ); ?>
                    </label>
                </li>

            <?php endforeach; ?>
        </ul>

        <input type="hidden" id="<?php echo $this->id; ?>" <?php $this->link(); ?> class="<?php echo $this->type . '-hidden'; ?>" value="<?php echo esc_attr( implode( ',', $multi_values ) ); ?>" />
    <?php }
}
//end
