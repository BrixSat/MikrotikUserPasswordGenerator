<?php


if (isset($_POST['numbervouchers']) && isset($_POST['prefixvouchers']) && isset($_POST['lenght']))
{
    require_once __DIR__ . '/coupon.php';
    require_once __DIR__ . '/generatePdf.php';
    require_once __DIR__ . '/mikrotik.php';

    $vouchers = [];

    for( $i=1; $i<=$_POST['numbervouchers']; $i++ )
    {
        $vouchers[$i] = strtolower(coupon::generate($_POST['lenght'], $_POST['prefixvouchers']));
    }


    if (isset($_POST['mikrotik']))
    {
        $mikrotik = insertIntoRouter($vouchers);
    }

    $pdfGenerated = generatePdf($vouchers, $_POST['prefixvouchers']);
}
?>
<!doctype html>
    <html>
    <head>
        <title>CampingAve!</title>
        <link rel="stylesheet" href="assets/css/bootstrap.min.css"> <!-- load bootstrap via CDN -->

        <script src="assets/js/jquery.min.js"></script> <!-- load jquery via CDN -->

    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-sm-12 ">
                    <h1>Vouchers generator:</h1>
                </div>
            </div>
            <!-- OUR FORM -->
            <form autocomplete="off" action="index.php" method="POST" >
                <div class="row">
                    <div class="col-md-12">
                         <div id="numbervouchers-group" class="form-group">
                            <label for="numbervouchers">Number of vouchers to generate:</label>
                            <input type="text" class="form-control" name="numbervouchers" placeholder="1" autocomplete="off" value="<?php echo $_POST['numbervouchers'];?>">
                        </div>
                        <div id="lenght-group" class="form-group">
                            <label for="lenght">Lenght of voucher:</label>
                            <input type="text" class="form-control" name="lenght" placeholder="2" autocomplete="off" value="<?php echo $_POST['lenght'];?>">
                        </div>
                        <div id="prefix-group" class="form-group">
                            <label for="prefix">Prefix of voucher:</label>
                            <input type="text" class="form-control" name="prefixvouchers" placeholder="camping-" autocomplete="off" value="<?php echo $_POST['prefixvouchers'];?>">
                        </div>
                        <div id="prefix-group" class="form-group">
                            <label for="prefix">Create account on mikrotik:</label>
                            <input type="checkbox" name="mikrotik"value="0">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="prefix-group" class="form-group">
                        <button type="submit" class="btn btn-success">Submit <span class="fa fa-arrow-right"></span></button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="prefix-group" class="form-group">
                        <?php
                        if(isset($pdfGenerated))
                        {
                        ?>
                            <object data="<?php echo $pdfGenerated; ?>" width="100%" height="900px"></object>
                        <?php
                        }
                        ?></div>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>

