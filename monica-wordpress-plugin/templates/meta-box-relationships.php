<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php if ( empty( $relationships['data'] ) ) : ?>
    <p><?php esc_html_e( 'No relationships found.', 'monica-integration' ); ?></p>
<?php else : ?>
    <ul>
        <?php foreach ( $relationships['data'] as $relationship ) : ?>
            <li>
                <?php echo esc_html( $relationship['relationship_type']['name'] ); ?>:
                <?php echo esc_html( $relationship['contact']['first_name'] . ' ' . $relationship['contact']['last_name'] ); ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h4><?php esc_html_e( 'Add New Relationship', 'monica-integration' ); ?></h4>
<form action="" method="post">
    <p>
        <label for="monica_relationship_type_id"><?php esc_html_e( 'Relationship Type', 'monica-integration' ); ?></label>
        <select id="monica_relationship_type_id" name="monica_relationship_type_id">
            <?php if ( ! is_wp_error( $relationship_types ) && ! empty( $relationship_types['data'] ) ) : ?>
                <?php foreach ( $relationship_types['data'] as $relationship_type ) : ?>
                    <option value="<?php echo esc_attr( $relationship_type['id'] ); ?>"><?php echo esc_html( $relationship_type['name'] ); ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </p>
    <p>
        <label for="monica_related_contact_id"><?php esc_html_e( 'Contact', 'monica-integration' ); ?></label>
        <select id="monica_related_contact_id" name="monica_related_contact_id">
            <?php if ( ! empty( $contacts ) ) : ?>
                <?php foreach ( $contacts as $contact ) : ?>
                    <?php
                    $monica_id = get_post_meta( $contact->ID, '_monica_contact_id', true );
                    if ( $monica_id ) :
                    ?>
                        <option value="<?php echo esc_attr( $monica_id ); ?>"><?php echo esc_html( $contact->post_title ); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </p>
    <input type="hidden" name="monica_contact_id" value="<?php echo esc_attr( $monica_contact_id ); ?>" />
    <input type="hidden" name="monica_post_id" value="<?php echo esc_attr( $post->ID ); ?>" />
    <?php wp_nonce_field( 'monica_add_relationship', 'monica_add_relationship_nonce' ); ?>
    <input type="submit" name="monica_add_relationship" class="button" value="<?php esc_attr_e( 'Add Relationship', 'monica-integration' ); ?>" />
</form>