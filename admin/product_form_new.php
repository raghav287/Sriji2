<?php
require_once 'check_session.php';
require_once 'db.php';

$page_title = "Add Product";
$product = null;
$variants = [];
$specs = [];
$images = [];
$all_colors = []; // Distinct colors from variants

if (isset($_GET['id'])) {
    $page_title = "Edit Product";
    $id = intval($_GET['id']);

    // Fetch Product
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        // Fetch ALL Variants (flattened, easier to work with)
        $var_sql = "SELECT v.*, s.size as variant_name, s.id as size_id 
                    FROM product_variants v 
                    JOIN product_sizes s ON v.product_size_id = s.id 
                    WHERE s.product_id = $id
                    ORDER BY s.size, v.color";
        $var_result = $conn->query($var_sql);
        while ($row = $var_result->fetch_assoc()) {
            $variants[] = $row;
            if (!empty($row['color']) && !in_array($row['color'], $all_colors)) {
                $all_colors[] = $row['color'];
            }
        }

        // Fetch Specs
        $s_sql = "SELECT * FROM product_specifications WHERE product_id = $id";
        $s_result = $conn->query($s_sql);
        while ($row = $s_result->fetch_assoc())
            $specs[] = $row;

        // Fetch Images (NOW with color column)
        $i_sql = "SELECT * FROM product_images WHERE product_id = $id ORDER BY is_primary DESC, color";
        $i_result = $conn->query($i_sql);
        while ($row = $i_result->fetch_assoc())
            $images[] = $row;
    }
}

// Fetch categories for dropdown
$cat_sql = "SELECT * FROM categories WHERE status='active' ORDER BY name";
$categories_result = $conn->query($cat_sql);
?>
<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        <?php echo $page_title; ?> - Admin
    </title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- STYLE CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/plugins.css" rel="stylesheet">

    <!-- ICONS CSS -->
    <link href="assets/css/icons.css" rel="stylesheet">

    <!-- FILE UPLOAD CSS -->
    <link href="assets/plugins/fileuploads/css/fileupload.css" rel="stylesheet" type="text/css" />

    <!-- SELECT2 -->
    <link href="assets/plugins/select2/select2.min.css" rel="stylesheet">

    <style>
        .variant-row {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .variant-row:hover {
            background: #f8f9fa;
        }

        .image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            margin: 5px;
            border-radius: 4px;
        }

        .color-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 4px;
            background: #f8f9fa;
            margin: 2px;
        }

        .color-swatch {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px #ddd;
        }

        .add-more-btn {
            margin-top: 10px;
        }

        #variantTable th {
            background: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .tab-content {
            min-height: 400px;
        }
    </style>
</head>

<body class="app sidebar-mini ltr light-mode">

    <!-- GLOBAL-LOADER -->
    <div id="global-loader">
        <img src="assets/images/loader.svg" class="loader-img" alt="Loader">
    </div>

    <div class="page">
        <div class="page-main">

            <!-- APP-SIDEBAR -->
            <?php include("includes/sidebar.php") ?>

            <!-- APP-CONTENT OPEN -->
            <div class="main-content app-content mt-0">
                <div class="side-app">

                    <!-- CONTAINER -->
                    <div class="main-container container-fluid">

                        <!-- PAGE-HEADER -->
                        <div class="page-header">
                            <h1 class="page-title">
                                <?php echo $page_title; ?>
                            </h1>
                            <div>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        <?php echo $page_title; ?>
                                    </li>
                                </ol>
                            </div>
                        </div>

                        <!-- ACTUAL FORM -->
                        <form action="product_save_new.php" method="POST" enctype="multipart/form-data" id="productForm">
                            <?php if ($product): ?>
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Product Information</h3>
                                        </div>
                                        <div class="card-body">

                                            <!-- TABS -->
                                            <ul class="nav nav-tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-bs-toggle="tab" href="#basic"
                                                        role="tab">
                                                        <i class="fe fe-info"></i> Basic Info
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#variants"
                                                        role="tab">
                                                        <i class="fe fe-grid"></i> Variants <span
                                                            class="badge bg-primary" id="variantCount">0</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#images" role="tab">
                                                        <i class="fe fe-image"></i> Images <span
                                                            class="badge bg-success" id="imageCount">0</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#specs" role="tab">
                                                        <i class="fe fe-list"></i> Specifications
                                                    </a>
                                                </li>
                                            </ul>

                                            <div class="tab-content mt-3">

                                                <!-- TAB 1: BASIC INFO -->
                                                <div class="tab-pane active" id="basic" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <label class="form-label">Product Name <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" name="name"
                                                                    value="<?php echo $product['name'] ?? ''; ?>"
                                                                    required>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="form-label">Description</label>
                                                                <textarea class="form-control" name="description"
                                                                    rows="4"><?php echo $product['description'] ?? ''; ?></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="form-label">Category <span
                                                                        class="text-danger">*</span></label>
                                                                <select class="form-control select2" name="category_id"
                                                                    required>
                                                                    <option value="">Select Category</option>
                                                                    <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                                                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($product['category_id']) && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($cat['name']); ?>
                                                                        </option>
                                                                    <?php endwhile; ?>
                                                                </select>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="form-label">Status</label>
                                                                <select class="form-control" name="status">
                                                                    <option value="active" <?php echo (isset($product['status']) && $product['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                                    <option value="inactive" <?php echo (isset($product['status']) && $product['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- TAB 2: VARIANTS -->
                                                <div class="tab-pane" id="variants" role="tabpanel">
                                                    <div class="mb-3">
                                                        <button type="button" class="btn btn-primary btn-sm"
                                                            onclick="addVariantRow()">
                                                            <i class="fe fe-plus"></i> Add Variant
                                                        </button>
                                                        <button type="button" class="btn btn-success btn-sm ms-2"
                                                            onclick="bulkAddVariants()">
                                                            <i class="fe fe-layers"></i> Bulk Add
                                                        </button>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover"
                                                            id="variantTable">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 15%">Size</th>
                                                                    <th style="width: 15%">Color</th>
                                                                    <th style="width: 15%">Price</th>
                                                                    <th style="width: 15%">Strike Price</th>
                                                                    <th style="width: 10%">Stock</th>
                                                                    <th style="width: 10%">SKU</th>
                                                                    <th style="width: 10%">Weight (g)</th>
                                                                    <th style="width: 5%"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="variantTableBody">
                                                                <?php if (!empty($variants)): ?>
                                                                    <?php foreach ($variants as $idx => $v): ?>
                                                                        <tr class="variant-row">
                                                                            <td>
                                                                                <input type="hidden"
                                                                                    name="variants[<?php echo $idx; ?>][id]"
                                                                                    value="<?php echo $v['id']; ?>">
                                                                                <input type="text"
                                                                                    class="form-control form-control-sm"
                                                                                    name="variants[<?php echo $idx; ?>][size]"
                                                                                    value="<?php echo $v['variant_name']; ?>"
                                                                                    placeholder="e.g. 2 Inch">
                                                                            </td>
                                                                            <td><input type="text"
                                                                                    class="form-control form-control-sm color-input"
                                                                                    name="variants[<?php echo $idx; ?>][color]"
                                                                                    value="<?php echo $v['color']; ?>"
                                                                                    placeholder="e.g. Blue"></td>
                                                                            <td><input type="number" step="0.01"
                                                                                    class="form-control form-control-sm"
                                                                                    name="variants[<?php echo $idx; ?>][price]"
                                                                                    value="<?php echo $v['price']; ?>"
                                                                                    placeholder="Base price"></td>
                                                                            <td><input type="number" step="0.01"
                                                                                    class="form-control form-control-sm"
                                                                                    name="variants[<?php echo $idx; ?>][strike_price]"
                                                                                    value="<?php echo $v['strike_price']; ?>"
                                                                                    placeholder="Original price"></td>
                                                                            <td><input type="number"
                                                                                    class="form-control form-control-sm"
                                                                                    name="variants[<?php echo $idx; ?>][stock]"
                                                                                    value="<?php echo $v['stock_quantity']; ?>"
                                                                                    placeholder="0"></td>
                                                                            <td><input type="text"
                                                                                    class="form-control form-control-sm"
                                                                                    name="variants[<?php echo $idx; ?>][sku]"
                                                                                    value="<?php echo $v['sku']; ?>"
                                                                                    placeholder="SKU"></td>
                                                                            <td><input type="number"
                                                                                    class="form-control form-control-sm"
                                                                                    name="variants[<?php echo $idx; ?>][weight]"
                                                                                    value="<?php echo $v['weight']; ?>"
                                                                                    placeholder="100"></td>
                                                                            <td><button type="button"
                                                                                    class="btn btn-sm btn-danger"
                                                                                    onclick="removeVariantRow(this)"><i
                                                                                        class="fe fe-trash-2"></i></button></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <!-- TAB 3: IMAGES -->
                                                <div class="tab-pane" id="images" role="tabpanel">
                                                    <div class="alert alert-info">
                                                        <strong>Tip:</strong> Upload images organized by color. Select
                                                        color association for each image to show the right images when
                                                        users select colors.
                                                    </div>

                                                    <div class="mb-3">
                                                        <button type="button" class="btn btn-primary btn-sm"
                                                            onclick="addImageRow()">
                                                            <i class="fe fe-upload"></i> Add Image
                                                        </button>
                                                    </div>

                                                    <div id="imageRowsContainer">
                                                        <?php if (!empty($images)): ?>
                                                            <?php foreach ($images as $idx => $img): ?>
                                                                <div class="image-row border rounded p-3 mb-2"
                                                                    data-index="<?php echo $idx; ?>">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-md-3">
                                                                            <img src="../assets/uploads/products/<?php echo $img['image_path']; ?>"
                                                                                class="image-preview border"
                                                                                alt="Product Image">
                                                                            <input type="hidden"
                                                                                name="existing_images[<?php echo $idx; ?>][id]"
                                                                                value="<?php echo $img['id']; ?>">
                                                                            <input type="hidden"
                                                                                name="existing_images[<?php echo $idx; ?>][path]"
                                                                                value="<?php echo $img['image_path']; ?>">
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Associated Color</label>
                                                                            <input type="text"
                                                                                class="form-control form-control-sm"
                                                                                name="existing_images[<?php echo $idx; ?>][color]"
                                                                                value="<?php echo $img['color']; ?>"
                                                                                placeholder="Leave blank for all colors">
                                                                        </div>
                                                                        <div class="col-md-2">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="checkbox"
                                                                                    name="existing_images[<?php echo $idx; ?>][is_primary]"
                                                                                    value="1" <?php echo $img['is_primary'] ? 'checked' : ''; ?>>
                                                                                <label class="form-check-label">Primary
                                                                                    Image</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-2">
                                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                                onclick="removeExistingImage(this, <?php echo $img['id']; ?>)">
                                                                                <i class="fe fe-trash-2"></i> Remove
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Hidden field for deleted images -->
                                                    <input type="hidden" id="deletedImages" name="deleted_images"
                                                        value="">
                                                </div>

                                                <!-- TAB 4: SPECIFICATIONS -->
                                                <div class="tab-pane" id="specs" role="tabpanel">
                                                    <div class="mb-3">
                                                        <button type="button" class="btn btn-primary btn-sm"
                                                            onclick="addSpecRow()">
                                                            <i class="fe fe-plus"></i> Add Specification
                                                        </button>
                                                    </div>

                                                    <div id="specRowsContainer">
                                                        <?php if (!empty($specs)): ?>
                                                            <?php foreach ($specs as $idx => $spec): ?>
                                                                <div class="row spec-row mb-2">
                                                                    <div class="col-md-5">
                                                                        <input type="hidden"
                                                                            name="specs[<?php echo $idx; ?>][id]"
                                                                            value="<?php echo $spec['id']; ?>">
                                                                        <input type="text" class="form-control"
                                                                            name="specs[<?php echo $idx; ?>][key]"
                                                                            value="<?php echo $spec['spec_key']; ?>"
                                                                            placeholder="e.g. Material">
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" class="form-control"
                                                                            name="specs[<?php echo $idx; ?>][value]"
                                                                            value="<?php echo $spec['spec_value']; ?>"
                                                                            placeholder="e.g. Cotton">
                                                                    </div>
                                                                    <div class="col-md-1">
                                                                        <button type="button" class="btn btn-sm btn-danger"
                                                                            onclick="removeSpecRow(this)"><i
                                                                                class="fe fe-trash-2"></i></button>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="card-footer text-end">
                                            <a href="products.php" class="btn btn-secondary">Cancel</a>
                                            <button type="submit" class="btn btn-primary ms-2">
                                                <i class="fe fe-save"></i> Save Product
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JQUERY -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- SIDE-MENU JS -->
    <script src="assets/plugins/sidemenu/sidemenu.js"></script>

    <!-- SELECT2 JS -->
    <script src="assets/plugins/select2/select2.full.min.js"></script>

    <!-- CUSTOM JS -->
    <script src="assets/js/custom.js"></script>

    <script>
        $(document).ready(function () {
            $('.select2').select2();
            updateCounts();
        });

        let variantIndex = <?php echo count($variants); ?>;
        let specIndex = <?php echo count($specs); ?>;
        let imageIndex = <?php echo count($images); ?>;
        let deletedImages = [];

        function addVariantRow() {
            const html = `
            <tr class="variant-row">
                <td><input type="text" class="form-control form-control-sm" name="variants[${variantIndex}][size]" placeholder="e.g. 2 Inch" required></td>
                <td><input type="text" class="form-control form-control-sm color-input" name="variants[${variantIndex}][color]" placeholder="e.g. Blue"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[${variantIndex}][price]" placeholder="Base price" required></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[${variantIndex}][strike_price]" placeholder="Original price"></td>
                <td><input type="number" class="form-control form-control-sm" name="variants[${variantIndex}][stock]" value="0" placeholder="0"></td>
                <td><input type="text" class="form-control form-control-sm" name="variants[${variantIndex}][sku]" placeholder="SKU"></td>
                <td><input type="number" class="form-control form-control-sm" name="variants[${variantIndex}][weight]" placeholder="100"></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeVariantRow(this)"><i class="fe fe-trash-2"></i></button></td>
            </tr>
        `;
            $('#variantTableBody').append(html);
            variantIndex++;
            updateCounts();
        }

        function removeVariantRow(btn) {
            $(btn).closest('tr').fadeOut(300, function () {
                $(this).remove();
                updateCounts();
            });
        }

        function addSpecRow() {
            const html = `
            <div class="row spec-row mb-2">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="specs[${specIndex}][key]" placeholder="e.g. Material">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="specs[${specIndex}][value]" placeholder="e.g. Cotton">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeSpecRow(this)"><i class="fe fe-trash-2"></i></button>
                </div>
            </div>
        `;
            $('#specRowsContainer').append(html);
            specIndex++;
        }

        function removeSpecRow(btn) {
            $(btn).closest('.spec-row').fadeOut(300, function () {
                $(this).remove();
            });
        }

        function addImageRow() {
            const html = `
            <div class="image-row border rounded p-3 mb-2">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="form-label">Upload Image</label>
                        <input type="file" class="form-control" name="new_images[${imageIndex}][file]" accept="image/*" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Associated Color</label>
                        <input type="text" class="form-control form-control-sm color-input" name="new_images[${imageIndex}][color]" placeholder="Leave blank for all colors">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="new_images[${imageIndex}][is_primary]" value="1">
                            <label class="form-check-label">Primary Image</label>
                        </div>
                    </div>
                    <div class="col-md-2 mt-4">
                        <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.image-row').remove(); updateCounts();">
                            <i class="fe fe-trash-2"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
            $('#imageRowsContainer').append(html);
            imageIndex++;
            updateCounts();
        }

        function removeExistingImage(btn, imageId) {
            if (confirm('Are you sure you want to delete this image?')) {
                deletedImages.push(imageId);
                $('#deletedImages').val(JSON.stringify(deletedImages));
                $(btn).closest('.image-row').fadeOut(300, function () {
                    $(this).remove();
                    updateCounts();
                });
            }
        }

        function updateCounts() {
            const variantCount = $('#variantTableBody tr').length;
            const imageCount = $('#imageRowsContainer .image-row').length;
            $('#variantCount').text(variantCount);
            $('#imageCount').text(imageCount);
        }

        function bulkAddVariants() {
            const sizes = prompt('Enter sizes separated by commas (e.g., 2 Inch, 4 Inch, 6 Inch):');
            const colors = prompt('Enter colors separated by commas (e.g., Red, Blue, Green):');

            if (!sizes) return;

            const sizeArray = sizes.split(',').map(s => s.trim()).filter(s => s);
            const colorArray = colors ? colors.split(',').map(c => c.trim()).filter(c => c) : [''];

            const basePrice = prompt('Enter base price for all variants:', '0');
            const strikePrice = prompt('Enter strike price (optional, leave blank to skip):', '');

            sizeArray.forEach(size => {
                colorArray.forEach(color => {
                    const html = `
                    <tr class="variant-row">
                        <td><input type="text" class="form-control form-control-sm" name="variants[${variantIndex}][size]" value="${size}" required></td>
                        <td><input type="text" class="form-control form-control-sm color-input" name="variants[${variantIndex}][color]" value="${color}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[${variantIndex}][price]" value="${basePrice}" required></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[${variantIndex}][strike_price]" value="${strikePrice}"></td>
                        <td><input type="number" class="form-control form-control-sm" name="variants[${variantIndex}][stock]" value="0"></td>
                        <td><input type="text" class="form-control form-control-sm" name="variants[${variantIndex}][sku]" placeholder="SKU"></td>
                        <td><input type="number" class="form-control form-control-sm" name="variants[${variantIndex}][weight]" placeholder="100"></td>
                        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeVariantRow(this)"><i class="fe fe-trash-2"></i></button></td>
                    </tr>
                `;
                    $('#variantTableBody').append(html);
                    variantIndex++;
                });
            });

            updateCounts();
            alert(`Added ${sizeArray.length * colorArray.length} variants!`);
        }
    </script>

</body>

</html>