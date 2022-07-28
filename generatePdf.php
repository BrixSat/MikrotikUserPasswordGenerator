<?php


function generatePdf($codes, $prefix)
{
	ini_set('max_execution_time', 300);
	ini_set('memory_limit', '1000M');

	/*
        To create the gift cards the first thing we need to do is create a image with this gift card
        and after that we create a pdf file with this image inside.
        I've tried to create the pdf with the text embeded but it did not work because the font could must to be outlined.
        More info: http://us.moo.com/help/faq/using-my-own-artwork.html
    */

	$value = 2;
	$file = '003';

	// Page size in millimetter - (Letter)
	$pageWidth = 297;
	$pageHeight = 420;

	$giftCardWidth = 123;
	$giftCardHeight = 60;

	$collumns = 2;
	$rows = 7;

	$marginPageTop = 5;
	$marginPageLeft = 25;

	// Image
	$marginTop = 2;
	$marginLeft = 5;
	$resizeWidth = -220;
	$resizeHeight = 0;


	// Fonts that will be used
	$fontOmnesBold = 'assets/fonts/OmnesBold.otf';
	$fontOmnes = 'assets/fonts/Omnes.otf';

	$count = 1;
	// Fist create all the images

	foreach ($codes as $code)
	{

		$code = str_replace(array("\r\n", "\r", "\n"), '', $code);

		// First lets create the image
		$image = imagecreatefrompng('assets/model_4/mini_giftcard_' . $value . '.png');

		// Antialiases
		imagealphablending($image, true);
		imageantialias($image, true);

		// Set the colors
		$white = imagecolorallocate($image, 255, 255, 255);
		$lightBrown = imagecolorallocate($image, 172, 169, 168);
		$black = imagecolorallocate($image, 0, 0 ,0);

		imagettftext($image, 60, 0, 350, 270, $black, $fontOmnesBold, $code);

		//	imagettftext( $image, 12, 0, 355, 275, $white, $fontOmnes, 'Connect to Wifi@CampingAve and use the code as uesernme and password.' );
		//	imagettftext( $image, 11, 0, 80, 375, $lightBrown, $fontOmnes, 'Valid for 15 days after first usage on Camping Ave.' );
		//	imagettftext( $image, 11, 0, 80, 395, $lightBrown, $fontOmnes, 'Not valid for past orders. Will not be replaced if lost or stolen. May be canceled any time without notice.' );

		imagesetthickness($image, 5);

		// Path where the image wil be saved
		$imgsrc = 'temp/' . $count . '.png';

		// header('Content-Type: image/png');
		// imagepng( $image );
		// exit;

		imagepng($image, $imgsrc);
		imagecolordeallocate($image, $white);

		// Destroy the image
		imagedestroy($image);

		$count++;
	}


	$count--;
	//Second create the pdf
	// PDF Library
	require('lib/fpdf.php');



	$giftCards = [];

	// Order in stacks
	for ($i = 0; $i < $count; $i++)
	{
		$giftCards[$i] = 'temp/' . ($i + 1) . '.png';
	}

	$slotsPerPage = ($collumns * $rows);
	$numberOfPages = ceil($count / $slotsPerPage);

	$totalGifts = $count;
	$giftsPerPosition = ceil($totalGifts / $numberOfPages);
	$left = $totalGifts % $numberOfPages;
	$perPosition = array();
	$giftCardsOrdered = array();
	if ($left != 0)
	{
		for ($i = 0; $i < $numberOfPages; $i++)
		{
			if ($left > 0)
			{
				$perPosition[$i] = $giftsPerPosition;
				$left--;
			} else
			{
				$perPosition[$i] = $giftsPerPosition - 1;
			}
		}
	} else
	{
		for ($i = 0; $i <= $numberOfPages; $i++)
		{
			$perPosition[$i] = $giftsPerPosition;
		}
	}
	$startsAt = array();
	$sum = 0;
	for ($i = 0; $i < sizeof($perPosition); $i++)
	{
		$startsAt[$i] = $sum;
		$sum = $sum + $perPosition[$i];
	}
	for ($i = 0; $i < $giftsPerPosition; $i++)
	{
		for ($j = 1; $j <= $numberOfPages; $j++)
		{
			if (sizeof($giftCardsOrdered) < sizeof($giftCards))
			{
				$index = $startsAt[$j - 1] + $i;
				$giftCardsOrdered[] = $giftCards[$index];
			}
		}
	}

    // Create the pdf
	$pdf = new FPDF('P', 'mm', array($pageWidth, $pageHeight));

	$pdf->AddPage();

	$row = 0;
	$collumn = 0;

	$page = 1;
	$giftCardsOnThisPage = 0;
	$totalGiftsCards = 0;
	$giftCardsOnThisRow = 0;

    // Draw vertical the cut lines
	$giftCardWidthBleed = 1;
	$giftCardHeightBleed = 1;
	$pdf->SetLineWidth(0.5);
	$pdf->SetDrawColor(0, 0, 0);
	for ($j = 0; $j < $collumns; $j++)
	{
		$lineStart = $marginPageLeft + ($j * $giftCardWidth) + $giftCardWidthBleed ;
		$pdf->Line($lineStart, 0, $lineStart, $pageHeight);
		$lineEnd = $marginPageLeft + ($j * $giftCardWidth) + $giftCardWidth - $giftCardWidthBleed;
		$pdf->Line($lineEnd, 0, $lineEnd, $pageHeight);
	}
	for ($j = 0; $j < $rows; $j++)
	{
		$lineStart = $marginPageTop + ($j * $giftCardHeight) + $giftCardHeightBleed;
		$pdf->Line(0, $lineStart, $pageWidth, $lineStart);
		//$lineEnd = $marginPageTop + ($j * $giftCardHeight) + $giftCardHeight - $giftCardHeightBleed;
		//$pdf->Line(0, $lineEnd, $pageWidth, $lineEnd);
	}

	for ($i = 0; $i < $count; $i++)
	{

		$imgsrc = $giftCardsOrdered[$i];

		//$positionY1 = ((($row -1) * ($giftCardHeight + $marginTop)) + $marginPageTop);
        $positionY=  $marginPageTop + ($row * $giftCardHeight) + $giftCardHeightBleed + $marginTop;
		//$positionX1 = ((($collumn -1) * ($giftCardWidth + $marginLeft)) + $marginPageLeft);
        $positionX = $marginPageLeft + ($collumn * $giftCardWidth) + $giftCardWidthBleed + $marginLeft ;
		$pdf->Image($imgsrc, $positionX , $positionY, $resizeWidth, $resizeHeight, 'PNG');

		$collumn++;
		if ($collumn > $collumns -1)
		{
			$collumn = 0;
            $row++;
		}

		$giftCardsOnThisPage++;
        $totalGiftsCards++;

		if ($giftCardsOnThisPage == ($collumns * $rows) && $totalGiftsCards < $count)
		{
			//continue;
			$pdf->AddPage();
			$giftCardWidthBleed = 1.5;
			$giftCardHeightBleed = 1.5;
			$pdf->SetLineWidth(0.5);
			$pdf->SetDrawColor(0, 0, 0);
			for ($j = 0; $j < $collumns; $j++)
			{
				$lineStart = $marginPageLeft + ($j * $giftCardWidth) + $giftCardWidthBleed;
				$pdf->Line($lineStart, 0, $lineStart, $pageHeight);
				$lineEnd = $marginPageLeft + ($j * $giftCardWidth) + $giftCardWidth - $giftCardWidthBleed;
				$pdf->Line($lineEnd, 0, $lineEnd, $pageHeight);
			}
			for ($j = 0; $j < $rows; $j++)
			{
				$lineStart = $marginPageTop + ($j * $giftCardHeight) + $giftCardHeightBleed;
				$pdf->Line(0, $lineStart, $pageWidth, $lineStart);
				$lineEnd = $marginPageTop + ($j * $giftCardHeight) + $giftCardHeight - $giftCardHeightBleed;
				$pdf->Line(0, $lineEnd, $pageWidth, $lineEnd);
			}
			$giftCardsOnThisPage = 0;
			$row = 1;
			$collumn = 1;
		}
	}

	$filename = 'Vouchers-' . $prefix . '-Total_' . $count . '_' . $numberOfPages . '.pdf';

	$pdf->Output('pdfs/' . $filename);
	// $pdf->Output();

	return 'pdfs/' . $filename;
}
