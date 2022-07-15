<?php

ini_set('max_execution_time', 300);

/*
	To create the gift cards the first thing we need to do is create a image with this gift card
	and after that we create a pdf file with this image inside.
	I've tried to create the pdf with the text embeded but it did not work because the font could must to be outlined.
	More info: http://us.moo.com/help/faq/using-my-own-artwork.html
*/

// Txt file with the codes
$codes = file( 'codes.txt' );

// Fonts that will be used
$fontOmnesBold = 'assets/fonts/OmnesBold.otf';

$count = 1;
// Fist create all the images

foreach ( $codes as  $code ){

	// First lets create the image
	// $image = imagecreatefrompng( 'assets/model_2/giftcard_back.png' );
	$image = imagecreatefrompng( 'assets/model_2/Crunchbutton_GiftCard_back_big.png' );

	// Antialiases
	imagealphablending( $image, true );
	imageantialias( $image, true );

	// Set the colors
	$white = imagecolorallocate( $image, 255, 255, 255 );

	// Put the texts
	imagettftext( $image, 67, 0, 890, 570, $white, $fontOmnesBold, 'crunchbutton.com/giftcard/' . $code );

	imagesetthickness ( $image, 5 );

	// Path where the image wil be saved
	$imgsrc = 'temp/' . $count . '.png';

	// header('Content-Type: image/png');
	// imagepng( $image );	
	// exit;

	imagepng( $image, $imgsrc );
	imagecolordeallocate( $image, $white );

	// Destroy the image
	imagedestroy( $image );

	$count++;
}

$count--;


//Second create the pdf
// PDF Library
require('lib/fpdf.php');

// Page size in millimetter - (Letter)
$pageWidth = 215;
$pageHeight = 279;

$giftCardWidth = 215;
$giftCardHeight = 90;

$collumns = 1;
$rows = 4;

$marginPageTop = 0;
$marginPageLeft = 0;

$marginTop = 0;
$marginLeft = 0;

$giftCards = [];

// Order in stacks
for ($i = 0; $i < $count; $i++) { 
	$giftCards[ $i ] = 'temp/' . ( $i + 1 ) . '.png';
}

$slotsPerPage = ( $collumns * $rows );
$numberOfPages = ceil( $count / $slotsPerPage );

$totalGifts = $count;
$giftsPerPosition = ceil( $totalGifts / $numberOfPages );
$left = $totalGifts % $numberOfPages;
$perPosition = array();
$giftCardsOrdered = array();
if( $left != 0 ){
	for( $i = 0; $i < $numberOfPages; $i++ ){
		if( $left > 0 ){
			$perPosition[ $i ] = $giftsPerPosition;
			$left--;
		} else {
			$perPosition[ $i ] = $giftsPerPosition - 1;
		}
	}
} else {
	for( $i = 0; $i <= $numberOfPages; $i++ ){
		$perPosition[ $i ] = $giftsPerPosition ;
	}
}
$startsAt = array();
$sum = 0;
for( $i = 0; $i < sizeof( $perPosition ); $i ++ ){
	$startsAt[ $i ] = $sum;
	$sum = $sum + $perPosition[ $i ];
}
for( $i = 0; $i < $giftsPerPosition; $i++ ){
	for( $j = 1; $j <= $numberOfPages; $j++ ){
		if( sizeof( $giftCardsOrdered ) < sizeof( $giftCards ) ){
			$index = $startsAt[ $j - 1 ] + $i;
			$giftCardsOrdered[] = $giftCards[ $index ];
		}
	}
}

// Create the pdf
$pdf = new FPDF( 'P', 'mm', array( $pageWidth, $pageHeight ) );

$pdf->AddPage();

$row = 1;
$collumn = 1;

$page = 1;
$giftCardsOnThisPage = 0;
$giftCardsOnThisRow = 0;

// Draw vertical the cut lines 
/*
$pdf->SetLineWidth( 0.01 );
$pdf->SetDrawColor( 175, 175, 175 );
for( $j = 0; $j <= $collumns; $j++ ){
	$l = $marginPageLeft + ( $j * $giftCardWidth );
	$pdf->Line( $l, 0, $l, $pageHeight);	
}
for( $j = 0; $j <= $rows; $j++ ){
	$t = $marginPageTop + ( $j * $giftCardHeight );
	$pdf->Line( 0, $t, $pageWidth, $t );
}
*/

for ( $i = 0; $i < $count; $i++ ) { 
	
	$imgsrc = $giftCardsOrdered[ $i ];

	$positionY = ( ( ( $row - 1 ) * ( $giftCardHeight + $marginTop ) ) + $marginPageTop );
	$positionX = ( ( ( $collumn - 1 ) * ( $giftCardWidth + $marginLeft ) ) + $marginPageLeft );
	
	$pdf->Image( $imgsrc, $positionX, $positionY, -300, 'PNG' );

	$collumn = ( $collumn == 1 ) ? 2 : 1;
	if( $collumn == 1 ){
		$row++;	
	}
	
	$giftCardsOnThisPage++;
	
	if( $giftCardsOnThisPage == ( $collumns * $rows ) ){
		$pdf->AddPage();
		/*
		for( $j = 0; $j <= $collumns; $j++ ){
			$l = $marginPageLeft + ( $j * $giftCardWidth );
			$pdf->Line( $l, 0, $l, $pageHeight);	
		}
		for( $j = 0; $j <= $rows; $j++ ){
			$t = $marginPageTop + ( $j * $giftCardHeight );
			$pdf->Line( 0, $t, $pageWidth, $t );
		}
		*/
		$giftCardsOnThisPage = 0;
		$row = 1;
		$collumn = 1;
	}
}



// $pdf->Output( 'pdfs/GiftCards.pdf' );
$pdf->Output();

// echo 'done!';
