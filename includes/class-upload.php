<?php

if (!defined('ABSPATH')) {
    exit;
}

class Astro_Space_Upload
{
    public function __construct()
    {
        add_shortcode('astro_space_upload', [$this, 'upload_form']);

        add_action(
            'admin_post_astro_space_public_upload',
            [$this, 'handle_upload']
        );

        add_action(
            'admin_post_nopriv_astro_space_public_upload',
            [$this, 'handle_upload']
        );
    }

    public function upload_form()
    {
        ob_start();

        $status = isset($_GET['astro_upload'])
            ? sanitize_key(wp_unslash($_GET['astro_upload']))
            : '';

        ?>

        <main class="astro-page">

            <section class="astro-upload-section">

                <div class="astro-container">

                    <div class="astro-section-heading">

                        <span class="astro-eyebrow">
                            IMAGE SUBMISSION
                        </span>

                        <h2>Upload Your Image</h2>

                        <p>
                            Select an astronomical image and provide
                            information about your discovery.
                        </p>

                    </div>

                    <?php if ($status === 'success') : ?>

                        <div class="astro-message success">
                            Image uploaded successfully!
                            Your submission is awaiting review.
                        </div>

                    <?php elseif ($status === 'error') : ?>

                        <div class="astro-message error">
                            Upload failed. Please check your image
                            and try again.
                        </div>

                    <?php endif; ?>

                    <div class="astro-upload-card">

                        <form
                            method="POST"
                            action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                            enctype="multipart/form-data"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="astro_space_public_upload"
                            >

                            <?php
                            wp_nonce_field(
                                'astro_space_upload',
                                'astro_space_nonce'
                            );
                            ?>

                            <div class="astro-form-group">

                                <label for="astro_title">
                                    Image Title
                                    <span>*</span>
                                </label>

                                <input
                                    type="text"
                                    id="astro_title"
                                    name="astro_title"
                                    placeholder="Example: The Andromeda Galaxy"
                                    maxlength="255"
                                    required
                                >

                            </div>

                            <div class="astro-form-group">

                                <label for="astro_description">
                                    Image Description
                                </label>

                                <textarea
                                    id="astro_description"
                                    name="astro_description"
                                    rows="4"
                                    placeholder="Tell us about this astronomical image..."
                                ></textarea>

                            </div>

                            <div class="astro-form-group">

                                <label>
                                    Select Your Image
                                    <span>*</span>
                                </label>

                                <label
                                    for="astro_space_image"
                                    class="astro-dropzone"
                                    id="astro-dropzone"
                                >

                                    <div class="astro-upload-icon">
                                        ↑
                                    </div>

                                    <strong>
                                        Drag and drop your image here
                                    </strong>

                                    <span>
                                        or click to browse your files
                                    </span>

                                    <small>
                                        JPG, PNG, WebP or GIF
                                    </small>

                                    <img
                                        id="astro-image-preview"
                                        class="astro-image-preview"
                                        alt="Selected image preview"
                                        hidden
                                    >

                                    <input
                                        type="file"
                                        id="astro_space_image"
                                        name="astro_space_image"
                                        accept=".jpg,.jpeg,.png,.webp,.gif"
                                        required
                                    >

                                </label>

                                <div
                                    id="astro-file-name"
                                    class="astro-file-name"
                                ></div>

                            </div>

                            <!-- Honeypot spam protection -->
                            <div class="astro-honeypot">
                                <input
                                    type="text"
                                    name="astro_website"
                                    tabindex="-1"
                                    autocomplete="off"
                                >
                            </div>

                            <button
                                type="submit"
                                class="astro-submit-button"
                            >
                                Upload Image
                                <span>→</span>
                            </button>

                            <p class="astro-privacy-note">
                                Your image will be submitted for review
                                before publication.
                            </p>

                        </form>

                    </div>

                </div>

            </section>

        </main>

        <?php

        return ob_get_clean();
    }

    public function handle_upload()
    {
        if (
            !isset($_POST['astro_space_nonce']) ||
            !wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_POST['astro_space_nonce'])
                ),
                'astro_space_upload'
            )
        ) {
            wp_die('Security verification failed.');
        }

        if (!empty($_POST['astro_website'])) {
            wp_die('Invalid submission.');
        }

        if (
            !isset($_FILES['astro_space_image']) ||
            $_FILES['astro_space_image']['error'] !== UPLOAD_ERR_OK
        ) {
            $this->redirect_with_status('error');
        }

        $file = $_FILES['astro_space_image'];

        $image_info = @getimagesize($file['tmp_name']);

        if (!$image_info) {
            $this->redirect_with_status('error');
        }

        $allowed_mimes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif'
        ];

        if (!in_array($image_info['mime'], $allowed_mimes, true)) {
            $this->redirect_with_status('error');
        }

        $title = isset($_POST['astro_title'])
            ? sanitize_text_field(wp_unslash($_POST['astro_title']))
            : '';

        $description = isset($_POST['astro_description'])
            ? sanitize_textarea_field(
                wp_unslash($_POST['astro_description'])
            )
            : '';

        if ($title === '') {
            $this->redirect_with_status('error');
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload(
            'astro_space_image',
            0,
            [
                'post_title' => $title
            ],
            [
                'test_form' => false
            ]
        );

        if (is_wp_error($attachment_id)) {
            $this->redirect_with_status('error');
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'astro_space_images';

        $saved = $wpdb->insert(
            $table_name,
            [
                'image_id'    => $attachment_id,
                'title'       => $title,
                'description' => $description,
                'status'      => 'pending',
                'created_at'  => current_time('mysql')
            ],
            [
                '%d',
                '%s',
                '%s',
                '%s',
                '%s'
            ]
        );

        if (!$saved) {
            wp_delete_attachment($attachment_id, true);
            $this->redirect_with_status('error');
        }

        $this->redirect_with_status('success');
    }

    private function redirect_with_status($status)
    {
        $redirect_url = wp_get_referer();

        if (!$redirect_url) {
            $redirect_url = home_url('/');
        }

        $redirect_url = add_query_arg(
            'astro_upload',
            $status,
            $redirect_url
        );

        wp_safe_redirect($redirect_url);
        exit;
    }
}