<?php

namespace App\Services;

use Exception;

class ProductPriceService 
{
  public function format(int $cents): string
  {
    if ($cents < 0) {
      throw new Exception('Price cannot be negative');
    } 

    return number_format($cents / 100, 2, ',', '.') . ' €';
  }
}