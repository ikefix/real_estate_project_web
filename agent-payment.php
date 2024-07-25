<?php include 'header.php'; ?>

<?php
if (!isset($_SESSION['agent'])) {
    header('location: ' . BASE_URL . 'agent-login');
    exit;
}

if (isset($_POST['form_paystack'])) {
    try {
        $statement = $pdo->prepare("SELECT * FROM packages WHERE id=?");
        $statement->execute([$_POST['package_id']]);
        $package = $statement->fetch(PDO::FETCH_ASSOC);

        if ($package) {
            $allowed_properties = $package['allowed_properties'];
            $_SESSION['package_id'] = $package['id'];
            $_SESSION['price'] = $package['price'];
            $_SESSION['allowed_days'] = $package['allowed_days'];

            $statement = $pdo->prepare("SELECT * FROM properties WHERE agent_id=?");
            $statement->execute([$_SESSION['agent']['id']]);
            $total_properties = $statement->rowCount();

            if ($allowed_properties != -1 && $total_properties > $allowed_properties) {
                unset($_SESSION['package_id'], $_SESSION['price'], $_SESSION['allowed_days']);
                throw new Exception('You are going to downgrade your package. Please delete some properties first so that it does not exceed the selected package\'s total allowed properties limit.');
            }
        } else {
            throw new Exception('Invalid package selected.');
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<div class="page-top" style="background-image: url('<?php echo BASE_URL; ?>uploads/banner.jpg')">
    <div class="bg"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Payment</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-content user-panel">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-12">
                <?php include 'agent-sidebar.php'; ?>
            </div>
            <div class="col-lg-9 col-md-12">
                <h4>Currently Active Plan</h4>

                <div class="row box-items mb-4">
                    <?php
                    $statement = $pdo->prepare("SELECT * FROM orders JOIN packages ON orders.package_id=packages.id WHERE agent_id=? AND currently_active=?");
                    $statement->execute([$_SESSION['agent']['id'], 1]);
                    $active_plans = $statement->fetchAll(PDO::FETCH_ASSOC);

                    if ($active_plans):
                        foreach ($active_plans as $plan):
                    ?>
                    <div class="col-md-4">
                        <div class="box1">
                            <h4>₦<?php echo $plan['price']; ?></h4>
                            <p><?php echo $plan['name']; ?></p>
                        </div>
                    </div>
                    <?php
                        endforeach;
                    else:
                    ?>
                    <div class="col-md-12 text-danger">
                        No Package Found.
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class='text-danger mb_20'><?php echo $error_message; ?></div>
                <?php endif; ?>

                <h4>Upgrade Plan (Make Payment)</h4>
                <div class="table-responsive">
                    <table class="table table-bordered upgrade-plan-table">
                        <tr>
                            <td>
                                <form id="paymentForm" action="" method="post">
                                    <select name="package_id" id="packageSelect" class="form-control select2" onchange="updatePrice()">
                                        <?php
                                        $statement = $pdo->prepare("SELECT * FROM packages ORDER BY id ASC");
                                        $statement->execute();
                                        $packages = $statement->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($packages as $package):
                                        ?>
                                            <option value="<?php echo $package['id']; ?>" data-price="<?php echo $package['price']; ?>"><?php echo $package['name']; ?> (₦<?php echo $package['price']; ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" id="packagePrice" name="package_price" value="<?php echo $packages[0]['price']; ?>">
                            </td>
                            <td>
                                <button type="button" class="btn btn-secondary btn-sm buy-button" onclick="payWithPaystack()">Make Payment</button>
                                </form>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
function updatePrice() {
    var packageSelect = document.getElementById('packageSelect');
    var selectedOption = packageSelect.options[packageSelect.selectedIndex];
    var price = selectedOption.getAttribute('data-price');
    document.getElementById('packagePrice').value = price;
}

function payWithPaystack() {
    var price = document.getElementById('packagePrice').value;
    var handler = PaystackPop.setup({
        key: 'pk_live_0feba4c7d10ad023602398b3f7cd6fb223b9e349',
        email: '<?php echo $_SESSION['agent']['email']; ?>',
        amount: price * 100, // Amount in kobo
        currency: 'NGN',
        ref: '' + Math.floor((Math.random() * 1000000000) + 1),
        callback: function(response) {
            window.location.href = 'verify_paystack.php?reference=' + response.reference;
        },
        onClose: function() {
            alert('Transaction was not completed, window closed.');
        }
    });
    handler.openIframe();
}

// Initialize the price with the first package's price
updatePrice();
</script>

<?php include 'footer.php'; ?>
