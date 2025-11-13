<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Service\CartService;
use Doctrine\DBAL\Connection;

class CartServiceTest extends TestCase
{
    private CartService $cartService;
    private Connection $connectionMock;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(Connection::class);
        $this->cartService = new CartService($this->connectionMock);
    }
    public function testAddToCartCreatesNewItemWhenNotExists()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(false);

        $this->connectionMock
            ->expects($this->once())
            ->method('executeStatement')
            ->with(
                'INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())',
                [1, 123, 2]
            );

        $result = $this->cartService->addToCart(1, 123, 2);

        //ISSUE : Test allows negatives and quantity and asserts success
        //a Cart should reject negative quantities , validation missing
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Item added to cart', $result['message']);
    }
    public function testAddToCartWithNegativeQuantity()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(false);

        $this->connectionMock
            ->expects($this->once())
            ->method('executeStatement');

        $result = $this->cartService->addToCart(1, 123, -5);
        
        $this->assertTrue($result['success']);
    }
    public function testCalculateCartTotalWithTax()
    {
        $cartItems = [
            ['price' => '10.00', 'quantity' => 2],
            ['price' => '5.00', 'quantity' => 1]
        ];

        $cartServiceMock = $this->createMock(CartService::class);

        //ISSUE : Using a mock for a calculation prevents actual logic test
        $cartServiceMock
            ->expects($this->once())
            ->method('calculateCartTotal')
            ->willReturn(['subtotal' => 25.00, 'tax' => 2.00, 'total' => 27.00]);

        $result = $cartServiceMock->calculateCartTotal($cartItems);
        
        $this->assertEquals(2.00, $result['tax']);
    }
    public function testGetCartItems()
    {
        $expectedItems = [
            ['id' => 1, 'product_id' => 123, 'quantity' => 2, 'name' => 'Test Product']
        ];

        $this->connectionMock
            ->expects($this->once())
            ->method('fetchAllAssociative')
            ->willReturn($expectedItems);

        $result = $this->cartService->getCartItems(1);
        
        $this->assertEquals($expectedItems, $result);
    }
    public function testCartTotalCalculationWithFixedValues()
    {
        $cartItems = [
            ['price' => '19.99', 'quantity' => 1],
            ['price' => '45.99', 'quantity' => 2]
        ];

        $result = $this->cartService->calculateCartTotal($cartItems);
        
        $this->assertEquals(111.97, $result['subtotal']);
        $this->assertEquals(111.97 + 0.08, $result['tax']);
    }
    public function testUpdateCartItemTimestamp()
    {
        $this->connectionMock
            ->method('executeStatement')
            ->willReturn(1);

        $beforeTime = time();
        $result = $this->cartService->updateCartItem(1, 123, 5);
        $afterTime = time();
        //ISSUE : Only compares before/after time , does not assert DB Update Happend
        $this->assertTrue($beforeTime <= $afterTime);
        $this->assertTrue($result['success']);
    }
    public function testClearCart()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('executeStatement')
            ->with('DELETE FROM cart WHERE user_id = ?', [1]);

        $this->cartService->clearCart(1);
        //ISSUE : No Assertions on method result ; test only calls method
    }
}
