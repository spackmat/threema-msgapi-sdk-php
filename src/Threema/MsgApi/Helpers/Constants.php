<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Helpers;

final class Constants
{
    public const MSGAPI_SDK_VERSION       = '1.0';
    public const MSGAPI_SDK_FEATURE_LEVEL = 3; // not used for anything important, but kept for backwards compatibility

    public const THREEMA_ID_LENGTH = 8;
    public const PUBLIC_KEY_PREFIX  = 'public:';
    public const PRIVATE_KEY_PREFIX = 'private:';
    public const DEFAULT_PINNED_KEY = 'sha256//8SLubAXo6MrrGziVya6HjCS/Cuc7eqtzw1v6AfIW57c=;sha256//8kTK9HP1KHIP0sn6T2AFH3Bq+qq3wn2i/OJSMjewpFw=';

    /**
     * create instance disabled
     */
    private function __construct()
    {
    }

    /**
     * clone instance disabled
     */
    private function __clone()
    {
    }
}
