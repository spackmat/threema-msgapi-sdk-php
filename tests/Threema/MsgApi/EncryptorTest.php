<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi;

use PHPUnit\Framework\TestCase;
use Threema\MsgApi\Encryptor\AbstractEncryptor;
use Threema\MsgApi\Encryptor\SodiumEncryptor;
use Threema\MsgApi\Helpers\KeyPrefix;
use Threema\MsgApi\Messages\TextMessage;

class EncryptorTest extends TestCase
{
    /**
     * @dataProvider getEncryptors
     */
    public function testCreateKeyPair(AbstractEncryptor $encryptor, string $prefix)
    {
        $this->assertNotNull($encryptor, $prefix . ' could not instance crypto tool');
        $keyPair = $encryptor->generateKeyPair();
        $this->assertNotNull($keyPair, $prefix . ': invalid key pair');
        $this->assertNotNull($keyPair->getPrivateKey(), $prefix . ': private key is null');
        $this->assertNotNull($keyPair->getPublicKey(), $prefix . ': public key is null');
    }

    /**
     * @dataProvider getEncryptors
     */
    public function testRandomNonce(AbstractEncryptor $encryptor, string $prefix)
    {
        $randomNonce = $encryptor->randomNonce();
        $this->assertEquals(24, strlen($randomNonce), $prefix . ': random nonce size not 24');
    }

    /**
     * @dataProvider getEncryptors
     */
    public function testDecrypt(AbstractEncryptor $encryptor, string $prefix)
    {
        $nonce = '0a1ec5b67b4d61a1ef91f55e8ce0471fee96ea5d8596dfd0';
        $box   = '45181c7aed95a1c100b1b559116c61b43ce15d04014a805288b7d14bf3a993393264fe554794ce7d6007233e8ef5a0f1ccdd704f34e7c7b77c72c239182caf1d061d6fff6ffbbfe8d3b8f3475c2fe352e563aa60290c666b2e627761e32155e62f048b52ef2f39c13ac229f393c67811749467396ecd09f42d32a4eb419117d0451056ac18fac957c52b0cca67568e2d97e5a3fd829a77f914a1ad403c5909fd510a313033422ea5db71eaf43d483238612a54cb1ecfe55259b1de5579e67c6505df7d674d34a737edf721ea69d15b567bc2195ec67e172f3cb8d6842ca88c29138cc33e9351dbc1e4973a82e1cf428c1c763bb8f3eb57770f914a';

        $privateKey = KeyPrefix::removePrivate(TestConstants::otherPrivateKey);
        $this->assertNotNull($privateKey);

        $publicKey = KeyPrefix::removePublic(TestConstants::myPublicKey);
        $this->assertNotNull($publicKey);

        $message = $encryptor->decryptMessage($encryptor->hex2bin($box),
            $encryptor->hex2bin($privateKey),
            $encryptor->hex2bin($publicKey),
            $encryptor->hex2bin($nonce));

        $this->assertNotNull($message);
        $this->assertTrue($message instanceof TextMessage);
        if ($message instanceof TextMessage) {
            $this->assertEquals($message->getText(), 'Dies ist eine Testnachricht. äöü');
        }
    }

    /**
     * @dataProvider getEncryptors
     */
    public function testEncrypt(AbstractEncryptor $encryptor, string $prefix)
    {
        $text  = 'Dies ist eine Testnachricht. äöü';
        $nonce = '0a1ec5b67b4d61a1ef91f55e8ce0471fee96ea5d8596dfd0';

        $privateKey = KeyPrefix::removePrivate(TestConstants::myPrivateKey);
        $this->assertNotNull($privateKey);

        $publicKey = KeyPrefix::removePublic(TestConstants::otherPublicKey);
        $this->assertNotNull($publicKey);

        $message = $encryptor->encryptMessageText($text,
            $encryptor->hex2bin($privateKey),
            $encryptor->hex2bin($publicKey),
            $encryptor->hex2bin($nonce));

        $this->assertNotNull($message);

        $box = $encryptor->decryptMessage($message,
            $encryptor->hex2bin(KeyPrefix::removePrivate(TestConstants::otherPrivateKey)),
            $encryptor->hex2bin(KeyPrefix::removePublic(TestConstants::myPublicKey)),
            $encryptor->hex2bin($nonce));

        $this->assertNotNull($box);
    }

    /**
     * @dataProvider getEncryptors
     */
    public function testDerivePublicKey(AbstractEncryptor $encryptor, string $prefix)
    {
        $publicKey   = $encryptor->derivePublicKey($encryptor->hex2bin(KeyPrefix::removePrivate(TestConstants::myPrivateKey)));
        $myPublicKey = $encryptor->hex2bin(KeyPrefix::removePublic(TestConstants::myPublicKey));

        $this->assertEquals($publicKey, $myPublicKey, $prefix . ' derive public key failed');
    }

    /**
     * @dataProvider getEncryptors
     */
    public function testEncryptImage(AbstractEncryptor $encryptor, string $prefix)
    {
        $threemaIconContent = file_get_contents(dirname(__FILE__) . '/threema.jpg');

        $privateKey = $encryptor->hex2bin(KeyPrefix::removePrivate(TestConstants::myPrivateKey));
        $publicKey  = $encryptor->hex2bin(KeyPrefix::removePublic(TestConstants::myPublicKey));

        $otherPrivateKey = $encryptor->hex2bin(KeyPrefix::removePrivate(TestConstants::otherPrivateKey));
        $otherPublicKey  = $encryptor->hex2bin(KeyPrefix::removePublic(TestConstants::otherPublicKey));

        $result = $encryptor->encryptImage($threemaIconContent, $privateKey, $otherPublicKey);

        $decryptedImage = $encryptor->decryptImage($result->getData(), $publicKey, $otherPrivateKey,
            $result->getNonce());

        $this->assertEquals($decryptedImage, $threemaIconContent, 'decryption of image failed');
    }

    /**
     * test hex2bin and bin2hex
     * @dataProvider getEncryptors
     */
    public function testHexBin(AbstractEncryptor $encryptor, string $prefix)
    {
        $testStr = TestConstants::myPrivateKeyExtract;

        // convert hex to bin
        $testStrBin = $encryptor->hex2bin($testStr);
        $this->assertNotNull($testStrBin);
        $testStrBinPhp = hex2bin($testStr);

        // compare usual PHP conversion with crypt tool version
        $this->assertEquals($testStrBin, $testStrBinPhp,
            $prefix . ': hex2bin returns different result than PHP-only implementation');

        // convert back to hex
        $testStrHex = $encryptor->bin2hex($testStrBin);
        $this->assertNotNull($testStrHex);
        $testStrHexPhp = bin2hex($testStrBin);

        // compare usual PHP conversion with crypt tool version
        $this->assertEquals($testStrHexPhp, $testStrHex,
            $prefix . ': bin2hex returns different result than PHP-only implementation');
        // compare with initial value
        $this->assertEquals($testStrHex, $testStr,
            $prefix . ': binary string is different than initial string after conversions');
    }

    public function getEncryptors(): iterable
    {
        // could add checks to see if it is supported
        yield SodiumEncryptor::class => [new SodiumEncryptor(), SodiumEncryptor::class];
    }
}
