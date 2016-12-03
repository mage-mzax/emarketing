<?php
/**
 * Mzax Emarketing (www.mzax.de)
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this Extension in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @version     {{version}}
 * @category    Mzax
 * @package     Mzax_Emarketing
 * @author      Jacob Siefer (jacob@mzax.de)
 * @copyright   Copyright (c) 2015 Jacob Siefer
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */



class Mzax_Emarketing_Model_Report_Seeder
{
    
    const NUMBER_OF_CAMPAIGNS = 1;
    
    
    protected $_campaigns;
    
    public function run()
    {
        $adapter = $this->getWriteAdapter();
        if (1) {
            $adapter->query("SET foreign_key_checks = 0;");
            $adapter->truncateTable($this->_getTable('mzax_emarketing/recipient'));
            $adapter->truncateTable($this->_getTable('mzax_emarketing/recipient_event'));
            $adapter->truncateTable($this->_getTable('mzax_emarketing/link_reference'));
            $adapter->truncateTable($this->_getTable('mzax_emarketing/link_reference_click'));
            $adapter->query("SET foreign_key_checks = 1;");
        }
        $this->generateAddresses();
        $this->generateUseragents();
        
        $this->_generateEntityIds(1, 100);
        $this->_generateRecipients(1);
       // $this->_generateRecipients(2);
        
        
        $this->_generateRecipientEvents();
        $this->_generateInboxEmail();
      
        $this->_generateTestLinks();
        $this->_generateLinkReferences();
        $this->_generateLinkClicks();
        
        
        
    }
    
    
    
    public function generateAddresses()
    {
        $adapter = $this->getWriteAdapter();
        
        
        $select = $adapter->select();
        $select->from($this->_getTable('customer/entity'), 'email');
        

        $table = $this->_getTable('recipient_address');
        $sql = "INSERT IGNORE INTO `$table` (`address`) \n$select";
        
        $adapter->query($sql);
        
        
        
        
    }
    
    
    
    
    
    public function generateUseragents()
    {
        $ua = <<<UA
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2; AskTB5.5; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/6.0; Microsoft Outlook 15.0.4420)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; InfoPath.3; Tablet PC 2.0; Microsoft Outlook 15.0.4481; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; Lotus-Notes/6.0; Windows-NT)
Mozilla/4.0 (compatible; Lotus-Notes/5.0; Windows-NT)
Mozilla/4.0 (compatible; Lotus-Notes/5.0; Macintosh PPC)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/536.26.14 (KHTML, like Gecko)
Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.9pre) Gecko/20100209 Shredder/3.0.2pre
Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.9pre) Gecko/20100303 Shredder/3.0.4pre
Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.9pre) Gecko/20100308 Lightning/1.0b1 Shredder/3.0.4pre
Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.14) Gecko/20080505 Thunderbird/2.0.0.14
Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.19) Gecko/20081209 Thunderbird/2.0.0.19
Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9pre) Gecko/2008050715 Thunderbird/3.0a1
Mozilla/5.0 (Windows; U; Windows NT 5.1; cs; rv:1.8.1.21) Gecko/20090302 Lightning/0.9 Thunderbird/2.0.0.21
Mozilla/5.0 (Windows; U; Windows NT 5.1; cs; rv:1.9.1.8) Gecko/20100227 Lightning/1.0b1 Thunderbird/3.0.3
Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.13) Gecko/20101208 Lightning/1.0b2 Thunderbird/3.1.7
Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.8) Gecko/20100802 Lightning/1.0b2 Thunderbird/3.1.2 ThunderBrowse/3.3.2
Mozilla/5.0 (Windows NT 6.1; rv:6.0) Gecko/20110812 Thunderbird/6.0
Mozilla/5.0 (X11; Linux i686; rv:7.0.1) Gecko/20110929 Thunderbird/7.0.1
Mozilla/5.0 (Windows NT 6.2; WOW64; rv:24.0) Gecko/20100101 Thunderbird/24.2.0
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; TmstmpExt)
Outlook-Express/7.0 (MSIE 7.0; Windows NT 5.1; Trident/4.0; AskTB5.6; TmstmpExt)
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; Media Center PC 6.0; OfficeLiveConnector.1.4; OfficeLivePatch.1.3; InfoPath.3; FDM; TmstmpExt)
Outlook-Express/7.0 (MSIE 6.0; Windows NT 5.1; SV1; GTB6.3; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; InfoPath.2; .NET CLR 3.0.04506.648; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; OfficeLiveConnector.1.3; OfficeLivePatch.0.0; TmstmpExt)
Outlook-Express/7.0 (MSIE 8; Windows NT 5.1; Trident/4.0; GTB7.0; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; TmstmpExt)
Outlook-Express/7.0 (MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; InfoPath.1; TmstmpExt)
Outlook-Express/7.0 (MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; HPDTDF; .NET4.0C; BRI/2; AskTbLOL/5.12.5.17640; TmstmpExt)
Opera/9.80 (S60; SymbOS; Opera Mobi/352; U; de) Presto/2.4.15 Version/10.00
Opera/9.80 (S60; SymbOS; Opera Mobi/499; U; es-ES) Presto/2.4.18 Version/10.00
Opera/9.80 (Windows Mobile; WCE; Opera Mobi/WMD-50430; U; en-GB) Presto/2.4.13 Version/10.00
SAMSUNG-GT-i8000/1.0 (Windows CE; Opera Mobi; U; en) Opera 9.5
HTC_HD2_T9193 Opera/9.7 (Windows NT 5.1; U; en) V1.49.841.1 (71528)
Opera/9.80 (Android; Linux; Opera Mobi/49; U; en) Presto/2.4.18 Version/10.00
Opera/9.80 (Android 2.2; Opera Mobi/-2118645896; U; pl) Presto/2.7.60 Version/10.5
Opera/9.80 (Android 2.2.2; Linux; Opera Tablet/ADR-1111101157; U; en) Presto/2.9.201 Version/11.50
Opera/9.80 (S60; SymbOS; Opera Tablet/9174; U; en) Presto/2.7.81 Version/10.5
Opera/9.80 (Windows NT 6.1; Opera Tablet/15165; U; en) Presto/2.8.149 Version/11.1
Mozilla/5.0 (Linux; Android 4.2.2; Nexus 7 Build/JDQ39) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.123 Safari/537.22 OPR/14.0.1025.52315
Mozilla/5.0 (Linux; Android 2.3.4; MT11i Build/4.0.2.A.0.62) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.123 Mobile Safari/537.22 OPR/14.0.1025.52315
Mozilla/5.0 (Linux; U; Android-4.0.3; en-us; Galaxy Nexus Build/IML74K) AppleWebKit/535.7 (KHTML, like Gecko) CrMo/16.0.912.75 Mobile Safari/535.7
Mozilla/5.0 (Linux; U; Android-4.0.3; en-us; Xoom Build/IML77) AppleWebKit/535.7 (KHTML, like Gecko) CrMo/16.0.912.75 Safari/535.7
Mozilla/5.0 (Linux; Android 4.0.4; SGH-I777 Build/Task650 & Ktoonsez AOKP) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Mobile Safari/535.19
Mozilla/5.0 (Linux; Android 4.1; Galaxy Nexus Build/JRN84D) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Mobile Safari/535.19
Mozilla/5.0 (iPhone; U; CPU iPhone OS 5_1_1 like Mac OS X; en) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/9B206 Safari/7534.48.3
Mozilla/5.0 (iPad; U; CPU OS 5_1_1 like Mac OS X; en-us) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/9B206 Safari/7534.48.3
Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1b2pre) Gecko/20081015 Fennec/1.0a1
Mozilla/5.0 (X11; U; Linux armv7l; en-US; rv:1.9.2a1pre) Gecko/20090322 Fennec/1.0b2pre
Mozilla/5.0 (Android; Linux armv7l; rv:9.0) Gecko/20111216 Firefox/9.0 Fennec/9.0
Mozilla/5.0 (Android; Mobile; rv:12.0) Gecko/12.0 Firefox/12.0
Mozilla/5.0 (Android; Mobile; rv:14.0) Gecko/14.0 Firefox/14.0
Mozilla/5.0 (Mobile; rv:14.0) Gecko/14.0 Firefox/14.0
Mozilla/5.0 (Mobile; rv:17.0) Gecko/17.0 Firefox/17.0
Mozilla/5.0 (Tablet; rv:18.1) Gecko/18.1 Firefox/18.1
Mozilla/5.0 (Android; Mobile; rv:28.0) Gecko/28.0 Firefox/28.0
Mozilla/5.0 (Android; Tablet; rv:29.0) Gecko/29.0 Firefox/29.0
Mozilla/5.0 (Macintosh; U; PPC Mac OS X; fi-fi) AppleWebKit/420+ (KHTML, like Gecko) Safari/419.3
Mozilla/5.0 (Macintosh; U; PPC Mac OS X; de-de) AppleWebKit/125.2 (KHTML, like Gecko) Safari/125.7
Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-us) AppleWebKit/312.8 (KHTML, like Gecko) Safari/312.6
Mozilla/5.0 (Windows; U; Windows NT 5.1; cs-CZ) AppleWebKit/523.15 (KHTML, like Gecko) Version/3.0 Safari/523.15
Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16
Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_6; it-it) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16
Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-HK) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5
Mozilla/5.0 (Windows; U; Windows NT 6.1; sv-SE) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4
Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/534.55.3 (KHTML, like Gecko) Version/5.1.3 Safari/534.53.10
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/536.26.17 (KHTML, like Gecko) Version/6.0.2 Safari/536.26.17
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/6.1.3 Safari/537.75.14
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/600.3.10 (KHTML, like Gecko) Version/8.0.3 Safari/600.3.10
Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1b3) Gecko/20090305 Firefox/3.1b3 GTB5
Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; ko; rv:1.9.1b2) Gecko/20081201 Firefox/3.1b2
Mozilla/5.0 (X11; U; SunOS sun4u; en-US; rv:1.9b5) Gecko/2008032620 Firefox/3.0b5
Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.8.1.12) Gecko/20080214 Firefox/2.0.0.12
Mozilla/5.0 (Windows; U; Windows NT 5.1; cs; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8
Mozilla/5.0 (X11; U; OpenBSD i386; en-US; rv:1.8.0.5) Gecko/20060819 Firefox/1.5.0.5
Mozilla/5.0 (Windows; U; Windows NT 5.0; es-ES; rv:1.8.0.3) Gecko/20060426 Firefox/1.5.0.3
Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.7.9) Gecko/20050711 Firefox/1.0.5
Mozilla/5.0 (Windows; Windows NT 6.1; rv:2.0b2) Gecko/20100720 Firefox/4.0b2
Mozilla/5.0 (X11; Linux x86_64; rv:2.0b4) Gecko/20100818 Firefox/4.0b4
Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2) Gecko/20100308 Ubuntu/10.04 (lucid) Firefox/3.6 GTB7.1
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b7) Gecko/20101111 Firefox/4.0b7
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b8pre) Gecko/20101114 Firefox/4.0b8pre
Mozilla/5.0 (X11; Linux x86_64; rv:2.0b9pre) Gecko/20110111 Firefox/4.0b9pre
Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b9pre) Gecko/20101228 Firefox/4.0b9pre
Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.2a1pre) Gecko/20110324 Firefox/4.2a1pre
Mozilla/5.0 (X11; U; Linux amd64; rv:5.0) Gecko/20100101 Firefox/5.0 (Debian)
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0a2) Gecko/20110613 Firefox/6.0a2
Mozilla/5.0 (X11; Linux i686 on x86_64; rv:12.0) Gecko/20100101 Firefox/12.0
Mozilla/5.0 (Windows NT 6.1; rv:15.0) Gecko/20120716 Firefox/15.0a2
Mozilla/5.0 (X11; Ubuntu; Linux armv7l; rv:17.0) Gecko/20100101 Firefox/17.0
Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20130328 Firefox/21.0
Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:22.0) Gecko/20130328 Firefox/22.0
Mozilla/5.0 (Windows NT 5.1; rv:25.0) Gecko/20100101 Firefox/25.0
Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:25.0) Gecko/20100101 Firefox/25.0
Mozilla/5.0 (Windows NT 6.1; rv:28.0) Gecko/20100101 Firefox/28.0
Mozilla/5.0 (X11; Linux i686; rv:30.0) Gecko/20100101 Firefox/30.0
Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/600.3.18 (KHTML, like Gecko)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:36.0) Gecko/20100101 Firefox/36.0
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/600.4.10 (KHTML, like Gecko) Version/8.0.4 Safari/600.4.10
Mozilla/5.0 (Windows NT 5.1; rv:11.0) Gecko Firefox/11.0 (via ggpht.com GoogleImageProxy)
Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_1 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D201
Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Mobile/11B554a
Mozilla/5.0 (iPhone; CPU iPhone OS 8_1_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B440
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; MDDRJS; Microsoft Outlook 14.0.6109; ms-office; MSOffice 14)
Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36
Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36
Mozilla/5.0 (iPhone; CPU iPhone OS 8_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12D508
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; InfoPath.3; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2; .NET4.0C; .NET4.0E; BRI/2; MSOffice 12)
Mozilla/5.0 (Linux; U; Android 4.4.2; en-au; SM-G900I Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Windows NT 6.1; rv:37.0) Gecko/20100101 Firefox/37.0
Mozilla/5.0 (Linux; Android 5.0.2; SM-T800 Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Safari/537.36
Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36
Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36
Mozilla/5.0 (iPad; CPU OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D257
Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; MATPJS; MSOffice 12)
Mozilla/5.0 (Linux; Android 5.1; Nexus 5 Build/LMY47I) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/40.0.0.0 Mobile Safari/537.36
Mozilla/5.0 (iPhone; CPU iPhone OS 8_1_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B466
Microsoft Office/14.0 (Windows NT 6.1; Microsoft Outlook 14.0.4760; Pro; ms-office; MSOffice 14)
Mozilla/5.0 (Windows NT 6.3; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0
Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) like Gecko
Mozilla/5.0 (iPhone; CPU iPhone OS 7_1 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D167
Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Mobile/10B329
Mozilla/5.0 (iPhone; CPU iPhone OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12A405
Mozilla/5.0 (Linux; U; Android 4.4.2; en-au; GT-I9505 Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Linux; U; Android 4.3; en-au; GT-I9505 Build/JSS15J) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36
Mozilla/5.0 (iPad; CPU OS 8_1_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B440
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/534.57.7 (KHTML, like Gecko)
Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)
Mozilla/5.0 (iPhone; CPU iPhone OS 8_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B411
Mozilla/5.0 (iPhone; CPU iPhone OS 8_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E; InfoPath.3; .NET CLR 1.1.4322; Microsoft Outlook 14.0.7113; ms-office; MSOffice 14)
Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.111 Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 3.0.04506.30; InfoPath.2; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET4.0C; .NET4.0E; AskTbFXTV5/5.9.1.14019; MSOffice 12)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:37.0) Gecko/20100101 Firefox/37.0
Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko)
Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D257
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2; .NET4.0C; .NET4.0E; MSOffice 12)
Mozilla/5.0 (iPhone; CPU iPhone OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12F70
Mozilla/5.0 (iPad; CPU OS 8_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B410
Mozilla/5.0 (Linux; U; Android 2.3.5; en-au; GT-I9100T Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
Mozilla/5.0 (iPad; CPU OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12F69
Mozilla/5.0 (Linux; Android 4.4.2; SM-G900FD Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.78.2 (KHTML, like Gecko)
Mozilla/5.0 (iPad; CPU OS 8_1_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B466
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MS-RTC LM 8; .NET4.0C; .NET4.0E; InfoPath.3; Microsoft Outlook 14.0.7109; ms-office; MSOffice 14)
Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_4 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Mobile/10B350
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/600.2.5 (KHTML, like Gecko) Version/7.1.2 Safari/537.85.11
Mozilla/5.0 (iPhone; CPU iPhone OS 8_1_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B436
Mozilla/5.0 (Linux; U; Android 4.4.3; en-au; HTC_PN071 Build/KTU84L) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:33.0) Gecko/20100101 Firefox/33.0
Mozilla/5.0 (Windows NT 6.0; rv:30.0) Gecko/20100101 Firefox/30.0
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; MSOffice 12)
Mozilla/5.0 (iPad; CPU OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12A405
Mozilla/5.0 (Linux; Android 4.4.4; SM-A300F Build/KTU84P) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/33.0.0.0 Mobile Safari/537.36
Mozilla/5.0 (iPad; CPU OS 8_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12D508
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/600.5.17 (KHTML, like Gecko)
Mozilla/5.0 (iPad; CPU OS 7_1 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D167
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36
Mozilla/5.0 (Windows NT 6.3; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0
Mozilla/5.0 (Linux; Android 4.4.2; SM-T535 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Safari/537.36
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0
Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12A365
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.59.10 (KHTML, like Gecko)
Mozilla/5.0 (Linux; U; Android 4.3; en-au; GT-N7105T Build/JSS15J) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; Microsoft Outlook 15.0.4693; ms-office; MSOffice 15)
Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)
Mozilla/5.0 (Linux; U; Android 4.4.2; en-au; SM-G800Y Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/5.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; InfoPath.2; .NET CLR 3.5.30729; .NET4.0C; .NET CLR 3.0.30729; AskTbBLT/5.14.1.20007; .NET4.0E; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Microsoft Outlook 15.0.4701; ms-office; MSOffice 15)
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; HPNTDF; .NET4.0C; .NET4.0E; TmstmpExt)
Mozilla/5.0 (Linux; Android 4.3; GT-I9300 Build/JSS15J) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.96 Mobile Safari/537.36
Mozilla/5.0 (iPad; CPU OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko)
Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; TAJB; rv:11.0) like Gecko
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; Microsoft Outlook 15.0.4701; Microsoft Outlook 15.0.4701; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7113; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/6.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; InfoPath.2; .NET4.0C; .NET4.0E; MSOffice 12)
Mozilla/5.0 (Linux; U; Android 4.3; en-au; GT-I9300T Build/JSS15J) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko
Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.115 Safari/537.36
Mozilla/5.0 (iPhone; CPU iPhone OS 6_1 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Mobile/10B142
SpamBayes/1.1a3+ (Image)
Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.2; .NET4.0E; TmstmpExt)
Mozilla/5.0 (Windows NT 6.3; Win64; x64; Trident/7.0; Touch; MSAppHost/2.0; rv:11.0) like Gecko
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/6.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2; .NET4.0C; .NET4.0E; Tablet PC 2.0; InfoPath.3)
Mozilla/5.0 (Linux; U; Android 4.4.4; en-au; SM-N915G Build/KTU84P) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Windows NT 5.1; rv:37.0) Gecko/20100101 Firefox/37.0
Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; MS-RTC LM 8; .NET4.0C; .NET4.0E)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/536.30.1 (KHTML, like Gecko)
Mozilla/5.0 (Linux; Android 4.4.2; SM-G900I Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36
Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Microsoft Outlook 15.0.4701; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MALN; .NET4.0C; .NET4.0E; Microsoft Outlook 15.0.4701; ms-office; MSOffice 15)
Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; en-us) AppleWebKit/530.19.2 (KHTML, like Gecko)
Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)
Mozilla/5.0 (iPhone; CPU iPhone OS 5_0_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko)
Mozilla/5.0 (iPhone; CPU iPhone OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12F70 Safari/600.1.4
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; MATP; MATP; MATPJS; Microsoft Outlook 15.0.4701; ms-office; MSOffice 15)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/600.1.25 (KHTML, like Gecko)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; ASU2JS; MSOffice 12)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/536.28.10 (KHTML, like Gecko)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; MATPJS; Microsoft Outlook 14.0.7113; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; TAJB; Microsoft Outlook 15.0.4701; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/600.3.18 (KHTML, like Gecko) Sparrow/1178
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.75.14 (KHTML, like Gecko)
Mozilla/5.0 (Linux; U; Android 4.2.2; en-au; GT-I9505 Build/JDQ39) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.74.9 (KHTML, like Gecko)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.77.4 (KHTML, like Gecko)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Tablet PC 2.0; .NET4.0C; .NET CLR 1.1.4322; .NET4.0E; InfoPath.3; Microsoft Outlook 15.0.4701; ms-office; MSOffice 15)
Mozilla/5.0 (Linux; U; Android 4.4.2; en-gb; SM-G900F Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-us) AppleWebKit/312.9 (KHTML, like Gecko)
Mozilla/5.0 (Linux; Android 4.3; en-au; SAMSUNG GT-I9505 Build/JSS15J) AppleWebKit/537.36 (KHTML, like Gecko) Version/1.5 Chrome/28.0.1500.94 Mobile Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2; .NET4.0C; .NET4.0E; MSOffice 12)
Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/7.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; InfoPath.3; ms-office; MSOffice 14)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9) AppleWebKit/537.71 (KHTML, like Gecko)
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E; TmstmpExt)
Mozilla/5.0 (Windows NT 6.3; Win64; x64; Trident/7.0; TAJB; MSAppHost/2.0; rv:11.0) like Gecko
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MATP; .NET4.0C; .NET4.0E; TmstmpExt)
Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; MDDSJS; rv:11.0) like Gecko
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Tablet PC 2.0; InfoPath.3; ASU2JS; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MDDS; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E; ms-office; MSOffice 14)
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; MATPJS; TmstmpExt)
Mozilla/5.0 (Linux; U; Android 4.3; en-au; GT-I9305 Build/JSS15J) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Linux; U; Android 5.0; en-gb; LG-D855 Build/LRX21R.A1419313395) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:11.0) Gecko/20120312 Thunderbird/11.0
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/7.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; MATP; Tablet PC 2.0; .NET4.0E; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/5.0 (iPad; CPU OS 7_1_1 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D201
Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.3; .NET4.0E; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/5.0 (Linux; U; Android 4.2.2; en-au; GT-I9507 Build/JDQ39) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (iPhone; CPU iPhone OS 8_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) GSA/5.2.43972 Mobile/12D508 Safari/600.1.4
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; HPDTDFJS; Microsoft Outlook 15.0.4701; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; Win64; x64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; HPNTDFJS; InfoPath.3; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; Touch; MASPJS; rv:11.0) like Gecko
Mozilla/5.0 (Windows NT 6.3; Win64; x64; Trident/7.0; Touch; MAARJS; MSAppHost/2.0; rv:11.0) like Gecko
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/600.1.17 (KHTML, like Gecko) Version/7.1 Safari/537.85.10
Mozilla/5.0 (Linux; Android 4.4.2; SM-T310 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Safari/537.36
Mozilla/5.0 (Linux; U; Android 4.4.2; en-au; GT-I9197 Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Windows NT 6.3; Win64; x64; Trident/7.0; MSAppHost/2.0; rv:11.0) like Gecko
Mozilla/5.0 (iPad; CPU OS 8_0 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12A365
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MAEM; .NET4.0C; .NET4.0E; BRI/2; TmstmpExt)
Mozilla/5.0 (Windows NT 6.0; rv:37.0) Gecko/20100101 Firefox/37.0
Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; Touch; TAJB; rv:11.0) like Gecko
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; InfoPath.3; Tablet PC 2.0; Microsoft Outlook 15.0.4569; Microsoft Outlook 15.0.4569; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Tablet PC 2.0; .NET4.0C; .NET CLR 1.1.4322; .NET4.0E; InfoPath.3; Microsoft Outlook 15.0.4701; Microsoft Outlook 15.0.4701; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7143; ms-office; MSOffice 14)
Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E; InfoPath.2; MSOffice 12)
Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/600.3.18 (KHTML, like Gecko) Version/8.0.3 Safari/600.3.18
Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_3 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Mobile/11B511
Mozilla/5.0 (Linux; U; Android 4.4.2; en-au; SM-N9005 Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)
Mozilla/5.0 (Linux; U; Android 4.4.2; en-us; SCH-I535 Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) like Gecko/20100101 Firefox/12.0
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; Trident/5.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.5.30729; InfoPath.2; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; FunWebProducts; InfoPath.3; .NET4.0E)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; InfoPath.3; Microsoft Outlook 15.0.4711; ms-office; MSOffice 15)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:7.0.1) Gecko/20140602 Postbox/3.0.11
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; HPNTDFJS; H9P; McAfee; Microsoft Outlook 15.0.4701; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; MATP; MATP; TAJB; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; TmstmpExt)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; GTB7.5; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; Tablet PC 2.0; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; BRI/2; TmstmpExt)
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MAAU; OfficeLiveConnector.1.5; OfficeLivePatch.1.3; .NET4.0C; .NET4.0E; TmstmpExt)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2; .NET4.0C; AskTbORJ/5.15.14.29495; Tablet PC 2.0; .NET4.0E; InfoPath.3; Microsoft Outlook 15.0.4701; ms-office; MSOffice 15)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_8) AppleWebKit/534.50.2 (KHTML, like Gecko)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; Tablet PC 2.0; BRI/2; .NET CLR 1.1.4322; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; InfoPath.2; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; NP06; BRI/2; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/5.0 (Linux; Android 4.4.2; en-gb; SAMSUNG SM-G900F Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Version/1.6 Chrome/28.0.1500.94 Mobile Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7134; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/7.0; .NET CLR 2.0.50727; SLCC2; .NET4.0C; .NET4.0E; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Microsoft Outlook 14.0.7113; ms-office; MSOffice 14)
Mozilla/5.0 (Linux; U; Android 4.4.4; en-au; HTC_0P6B Build/KTU84P) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Linux; U; Android 4.4.2; en-au; GT-I9507 Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Linux; Android 4.4.2; GT-I9506 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36
Mozilla/5.0 (Linux; Android 5.1; Nexus 5 Build/LMY47I; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/42.0.2311.129 Mobile Safari/537.36
Mozilla/5.0 (Linux; Android 4.4.2; GT-I9505 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36
Mozilla/5.0 (Linux; U; Android 4.4.2; fi-fi; GT-I9195 Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Linux; U; Android 4.1.2; en-au; GT-P3110 Build/JZO54K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30
Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_6 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Mobile/11B651
Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.6117; ms-office; MSOffice 14)
Mozilla/5.0 (Linux; Android 5.0.2; SM-G920I Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile Safari/537.36
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36
Mozilla/5.0 (Linux; Android 4.4.2; en-au; SAMSUNG SM-G900I Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Version/1.6 Chrome/28.0.1500.94 Mobile Safari/537.36
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; McAfee; BRI/2; TmstmpExt)
Mozilla/5.0 (Linux; U; Android 4.0.4; en-au; T-Hub2 Build/TVA301TELBG3) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30
Mozilla/5.0 (Linux; U; Android 4.1.2; en-au; GT-I9305 Build/JZO54K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_2 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Mobile/11A501
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.52.7 (KHTML, like Gecko)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; InfoPath.2; BRI/2)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; Tablet PC 2.0; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/5.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 1.1.4322; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7143; ms-office; MSOffice 14)
Mozilla/5.0 (iPhone; CPU iPhone OS 8_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12B411 Safari/600.1.4
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MATP; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/6.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; InfoPath.3; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022; InfoPath.2; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET4.0C; .NET4.0E; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Tablet PC 2.0; InfoPath.3; TAJB; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; HPDTDF; .NET CLR 1.1.4322; .NET4.0C; .NET4.0E; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; BRI/2; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; BRI/1; .NET4.0C; .NET4.0E; MALC; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/536.30.1 (KHTML, like Gecko)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET CLR 1.1.4322; .NET4.0C; .NET4.0E; MALC; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/7.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MAAR; Tablet PC 2.0; .NET4.0C; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.1; .NET4.0E)
Mozilla/5.0 (Linux; U; Android 4.2.2; en-au; HTC One XL Build/JDQ39) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; BRI/1; MDDC; InfoPath.2; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2; .NET4.0C; MS-RTC EA 2; .NET4.0E; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; eSobiSubscriber 1.0.0.40; .NET4.0C; .NET4.0E; MAAR; InfoPath.3; BRI/2; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/5.0 (iPad; CPU OS 7_0_3 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Mobile/11B511
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; GTB7.5; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; InfoPath.1)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; BRI/2; .NET4.0E; InfoPath.3; .NET CLR 1.1.4322; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; BRI/2; AskTbORJ/5.15.23.36191; MSOffice 12)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.59.10 (KHTML, like Gecko) Version/5.1.9 Safari/534.59.10
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; CMDTDFJS; F9J; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Thunderbird/31.6.0
Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:37.0) Gecko/20100101 Firefox/37.0
Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0)
Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)
Mozilla/5.0 (Linux; Android 5.0.1; SM-N915G Build/LRX22C) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/5.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.5.30729; InfoPath.1; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10) AppleWebKit/600.1.25 (KHTML, like Gecko) Version/8.0 Safari/600.1.25
Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0)
Mozilla/5.0 (Linux; Android 4.4.4; SM-G360G Build/KTU84P) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/33.0.0.0 Mobile Safari/537.36
Mozilla/5.0 (Linux; U; Android 4.4.2; en-nz; SM-G800Y Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Windows NT 6.1; Trident/7.0; MATP; rv:11.0) like Gecko
Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36
Mozilla/5.0 (Windows NT 6.1; rv:30.0) Gecko/20100101 Firefox/30.0
Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/600.5.17 (KHTML, like Gecko) Version/8.0.5 Safari/600.5.17
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/600.5.17 (KHTML, like Gecko) Version/7.1.5 Safari/537.85.14
Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.76 Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows Phone OS 7.0; Trident/3.1; IEMobile/7.0; HTC; C625b)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10) AppleWebKit/600.1.25 (KHTML, like Gecko)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7113; ms-office; MSOffice 14)
Mozilla/5.0 (iPhone; CPU iPhone OS 8_1_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B435
Mozilla/5.0 (Linux; U; Android 4.4.4; en-au; SM-A500Y Build/KTU84P) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Thunderbird/31.5.0 Lightning/3.3.2
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Tablet PC 2.0; InfoPath.3; Microsoft Outlook 15.0.4420; Microsoft Outlook 15.0.4420; ms-office; MSOffice 15)
Mozilla/5.0 (iPhone; CPU iPhone OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; Microsoft Outlook 15.0.4711; Microsoft Outlook 15.0.4711; ms-office; MSOffice 15)
Outlook-Express/7.0 (MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; CPNTDF; Tablet PC 2.0; OfficeLiveConnector.1.5; OfficeLivePatch.1.3; .NET4.0C; .NET4.0E; TmstmpExt)
Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko)
Mozilla/5.0 (Linux; U; Android 4.4.2; en-gb; SM-T530 Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30
Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko/20100101 Firefox/12.0
Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36
Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/7.0)
Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; MAAU; rv:11.0) like Gecko
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; Microsoft Outlook 15.0.4711; ms-office; MSOffice 15)
Mozilla/5.0 (Linux; Android 5.1; Nexus 5 Build/LMY47I; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/42.0.2311.137 Mobile Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MATP; BRI/1; .NET4.0C; .NET4.0E; InfoPath.3; Microsoft Outlook 15.0.4711; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Microsoft Outlook 15.0.4569; ms-office; MSOffice 15)
Mozilla/5.0 (Linux; Android 5.0.2; D5833 Build/23.1.A.0.726; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/42.0.2311.138 Mobile Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/6.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; CognosRCP; InfoPath.3; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7109; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/6.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; InfoPath.3; Tablet PC 2.0; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7109; ms-office; MSOffice 14)
Mozilla/5.0 (Windows NT 5.1; rv:24.0) Gecko/20100101 Firefox/24.0
Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; MATP; MATP; rv:11.0) like Gecko
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/6.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET CLR 1.1.4322; .NET4.0C; .NET4.0E; InfoPath.3; Microsoft Outlook 15.0.4667; Microsoft Outlook 15.0.4667; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Tablet PC 2.0; InfoPath.3; Microsoft Outlook 15.0.4569; Microsoft Outlook 15.0.4569; Microsoft Outlook 15.0.4569; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Tablet PC 2.0; InfoPath.3; Microsoft Outlook 15.0.4569; Microsoft Outlook 15.0.4569; Microsoft Outlook 15.0.4569; Microsoft Outlook 15.0.4569; ms-office; MSOffice 15)
Mozilla/5.0 CK={tiPpw0h4g7nFGp72gZJoeCDClzkyE8VoRPJNONHzqnoSYX6sp01H7HKtN+GE27xDhnOlL0uwFg1ltoIol/g0+L64RZ5bfxaISVl+/wD1cwxBHSzrsni6Pg==} (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7113; ms-office; MSOffice 14)
Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36
Mozilla/5.0 (iPad; CPU OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Mobile/11B554a
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0
Mozilla/5.0 (Linux; Android 5.0.2; SM-G925I Build/LRX22G; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/42.0.2311.138 Mobile Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Tablet PC 2.0; Microsoft Outlook 15.0.4711; ms-office; MSOffice 15)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; HPDTDFJS; Microsoft Outlook 15.0.4711; Microsoft Outlook 15.0.4711; ms-office; MSOffice 15)
Mozilla/5.0 (iPad; CPU OS 8_1_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B435
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Tablet PC 2.0; InfoPath.3; Microsoft Outlook 15.0.4569; ms-office; MSOffice 15)
Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_5 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Mobile/11B601
Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:37.0) Gecko/20100101 Firefox/37.0
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/600.6.3 (KHTML, like Gecko) Version/7.1.6 Safari/537.85.15
Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; CMDTDFJS; F9J; Microsoft Outlook 14.0.7149; ms-office; MSOffice 14)
Mozilla/5.0 (Linux; Android 5.0.1; SM-N910P Build/LRX22C; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/42.0.2311.138 Mobile Safari/537.36
Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)
Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; Touch; MDDCJS; rv:11.0) like Gecko
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Thunderbird/31.6.0 Lightning/3.3.2
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.65 Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Tablet PC 2.0; McAfee; InfoPath.3; MATPJS; Microsoft Outlook 15.0.4719; ms-office; MSOffice 15)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.77.4 (KHTML, like Gecko) Version/7.0.5 Safari/537.77.4
Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36
Mozilla/5.0 (iPad; CPU OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12F69 Safari/600.1.4
Mozilla/5.0 (Linux; U; Android 4.3; en-nz; GT-I9300 Build/JSS15J) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Tablet PC 2.0; InfoPath.3; Microsoft Outlook 15.0.4569; Microsoft Outlook 15.0.4569; ms-office; MSOffice 15)
Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:37.0) Gecko/20100101 Firefox/37.0
Mozilla/5.0 (iPhone; CPU iPhone OS 7_0 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Mobile/11A465
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36
Mozilla/5.0 (Linux; Android 5.0.1; HTC_0P6B Build/LRX22C; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/42.0.2311.138 Mobile Safari/537.36
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Thunderbird/31.6.0
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; InfoPath.3; Microsoft Outlook 14.0.7149; ms-office; MSOffice 14)
Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; MATP; rv:11.0) like Gecko
Mozilla/5.0 (Linux; U; Android 4.1.2; en-au; GT-I8730T Build/JZO54K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
Mozilla/5.0 (iPad; CPU OS 5_1_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; .NET4.0C; .NET4.0E; CognosRCP; InfoPath.3; Media Center PC 6.0; SLCC2; Microsoft Outlook 14.0.7147; ms-office; MSOffice 14)
Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0; MSAppHost/1.0)
Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.65 Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; InfoPath.2; .NET4.0C; .NET4.0E; MSOffice 12)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; InfoPath.3; .NET4.0E; Microsoft Outlook 14.0.7149; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; MATP; MATP; TAJB; Microsoft Outlook 14.0.7149; ms-office; MSOffice 14)
Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)
Mozilla/5.0 (Windows NT 6.3; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; Tablet PC 2.0; InfoPath.3; Microsoft Outlook 15.0.4719; ms-office; MSOffice 15)
Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.81 Safari/537.36
Mozilla/5.0 (Windows NT 6.1; rv:38.0) Gecko/20100101 Firefox/38.0
Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.81 Safari/537.36
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/7.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; Microsoft Outlook 14.0.7149; ms-office; MSOffice 14)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; WOW64; Trident/7.0; .NET4.0E; .NET4.0C; .NET CLR 3.5.30729; .NET CLR 2.0.50727; .NET CLR 3.0.30729; MAARJS; Microsoft Outlook 15.0.4719; ms-office; MSOffice 15)
Mozilla/5.0 (Linux; Android 5.0.2; SM-G920I Build/LRX22G; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/42.0.2311.138 Mobile Safari/537.36
UA;
        
        $ua = explode("\n", $ua);
        
        /* @var $resource Mzax_Emarketing_Model_Resource_Useragent */
        $resource = Mage::getResourceSingleton('mzax_emarketing/useragent');
        
        foreach ($ua as $useragent) {
            $resource->getUserAgentId($useragent);
        }
        $resource->parse();
        
    }
    
    
    
    public function _generateLinkClicks()
    {
        $adapter = $this->getWriteAdapter();
        
        $select = $adapter->select();
        $select->from($this->_getTable('link_reference', 'ref'), null);
        $select->join($this->_getTable('recipient_event', 'event'), 'event.recipient_id = ref.recipient_id', null);
        $select->join($this->_getTable('recipient', 'recipient'), 'recipient.recipient_id = ref.recipient_id', null);
        $select->columns(array(
            'reference_id' => 'ref.reference_id',
            'clicked_at'   => 'event.captured_at'
        ));
        
        $table = $this->_getTable('link_reference_click');
        $sql = "INSERT INTO `$table` (`reference_id`, `clicked_at`) \n$select";
        
        $start =  microtime(true);
        $stmt = $adapter->query($sql);
        $duration =  microtime(true) - $start;
        
        $this->log(sprintf('Generation of %s link reference clicks took %01.4fsec', $stmt->rowCount(), $duration), '+1');
        
        
    }
    
    
    
    public function _generateLinkReferences()
    {
        $adapter = $this->getWriteAdapter();
        
        $select = $adapter->select();
        $select->from($this->_getTable('link'), 'link_id');
        $select->where('`url` LIKE "http://example.com/%"');
        
        $linkIds = $adapter->fetchCol($select);
        
        $randomLinkId = new Zend_Db_Expr(sprintf("ELT(FLOOR(1 + (RAND()*%s)), %s)", count($linkIds), implode(',', $linkIds)));
        
        $select = $adapter->select();
        $select->from($this->_getTable('recipient'), null);
        $select->where('RAND() > 0.9');
        
        $select->columns(array(
            'recipient_id' => 'recipient_id',
            'link_id' => $randomLinkId,
            'public_id' => 'MD5(CONCAT("asdfasdf", recipient_id, RAND(), RAND()))'
        ));
        
        $table = $this->_getTable('link_reference');
        
        $sql = "INSERT INTO `$table` (`recipient_id`, `link_id`, `public_id`) \n$select";
        
        
        $start =  microtime(true);
        $stmt = $adapter->query($sql);
        $duration =  microtime(true) - $start;
        
        $this->log(sprintf('Generation of %s link references took %01.4fsec', $stmt->rowCount(), $duration), '+1');
    }
    
    
    
    public function _generateTestLinks()
    {
        $table = $this->getResourceHelper()->getTable('mzax_emarketing/link');
        $adapter = $this->getWriteAdapter();
        $adapter->delete($table, '`url` LIKE "http://example.com/%"');
        
        $inserts = array();
        for($i = 0; $i < 200; $i++) {
            $anchor = "Click Me $i";
            $url    = "http://example.com/myproduct?id=".($i%20);
            $optout = (mt_rand(0,100) < 5) ? '1': '0';
            $hash   = md5($url.$anchor);
            
            $inserts[] = "('$hash', '$url', '$anchor', $optout)";
        }
        
        $inserts = implode(",\n", $inserts);
        $sql = "INSERT INTO `$table` (`link_hash`, `url`, `anchor`, `optout`) VALUES\n$inserts";
        $adapter->query($sql);
    }
    
    
    
    
    
    
    public function _generateRecipients($campaignId, $clear = false)
    {
        $table = $this->getResourceHelper()->getTable('mzax_emarketing/recipient');
        
        $adapter = $this->getWriteAdapter();
        if ($clear) {
            $adapter->query("SET foreign_key_checks = 0;");
            $adapter->truncateTable($table);
            $adapter->query("SET foreign_key_checks = 1;");
        }
        
        $variations = $adapter->select()->from($this->_getTable('campaign_variation'), 'variation_id')->where('campaign_id = ?', $campaignId);
        $variations = $adapter->fetchCol($variations);
        $variations[] = '0';
        $variations[] = '-1';
        
        $columns = array(
            'created_at'    => 'NOW()',
            'sent_at'       => 'DATE_ADD("2009-07-01", INTERVAL FLOOR(1000 + (RAND() * 60*24*300)) MINUTE)',
            'prepared_at'   => 'NOW()',
            'object_id'     => 'entity_id',
            'campaign_id'   => new Zend_Db_Expr($campaignId),
            'variation_id'  => $this->getRandomValueExpr($variations)
        );
        
        $select = $adapter->select();
        $select->from('temp_entities', null);
        $select->columns($columns);
        
        $sql = $adapter->insertFromSelect($select, $table, array_keys($columns));
        
        $adapter->query("SET foreign_key_checks = 0;");
        $start =  microtime(true);
        $stmt = $adapter->query($sql);
        $duration =  microtime(true) - $start;
        $adapter->query("SET foreign_key_checks = 1;");
        
        $this->log(sprintf('Generation of %s recipients took %01.4fsec', $stmt->rowCount(), $duration), '+1');
    }
    
    

    public function _generateRecipientEvents()
    {
        $table = $this->getResourceHelper()->getTable('mzax_emarketing/recipient_event');
        $adapter = $this->getWriteAdapter();
        $adapter->query("SET foreign_key_checks = 0;");
        $adapter->truncateTable($table);
        $adapter->query("SET foreign_key_checks = 1;");
        
        $this->_generateRecipientEvent(Mzax_Emarketing_Model_Recipient::EVENT_TYPE_VIEW, 0.8, 0.2);
        $this->_generateRecipientEvent(Mzax_Emarketing_Model_Recipient::EVENT_TYPE_CLICK, 0.3, 0.2);
        
    }
    
    
    public function _generateRecipientEvent($eventType, $rate, $rate2)
    {
        $table = $this->getResourceHelper()->getTable('mzax_emarketing/recipient_event');
        $adapter = $this->getWriteAdapter();
        
        $columns = array(
            'event_type'    => new Zend_Db_Expr($eventType),
            'recipient_id'  => 'recipient_id',
            'captured_at'   => 'DATE_ADD(`sent_at`, INTERVAL FLOOR(30 + (RAND() * 1500*10)) MINUTE)',
            'useragent_id'  => new Zend_Db_Expr('FLOOR(1+(RAND()*105))'),
            'ip'            => new Zend_Db_Expr("INET_ATON(CONCAT_WS('.', FLOOR(3+(RAND()*240)),FLOOR(1+(RAND()*250)),FLOOR(1+(RAND()*250)),FLOOR(1+(RAND()*254))))"),
            'country_id'    => $this->getRandomValueExpr('DE', 'AU', 'US', 'FR', 'ES', 'CH', 'DK', 'EC', 'GB', 'CA'),
            'region_id'     => $this->getRandomValueExpr(
                    'DE-BW', 'DE-BY', 'DE-BE', 'DE-BB', 'DE-HB', 'DE-HH', 
                    'DE-HE', 'DE-MV', 'DE-NI', 'DE-NW', 'DE-RP', 'DE-SL', 
                    'DE-SN', 'DE-ST', 'DE-SH', 'DE-TH', 
                    'US-KS', 'US-MA', 'US-NE', 'US-NC', 'US-ID', 'US-NM', 
                    'US-TN', 'US-UT', 'US-WA')
                
        );
        
        $select = $adapter->select();
        $select->from($this->getResourceHelper()->getTable('mzax_emarketing/recipient'), null);
        $select->columns($columns);
        $select->where('RAND() < ?', $rate);
        
        $sql = $adapter->insertFromSelect($select, $table, array_keys($columns));
        
        $start =  microtime(true);
        $stmt = $adapter->query($sql);
        $duration =  microtime(true) - $start;
        
        $this->log(sprintf('Generation of %s recipient events took %01.4fsec', $stmt->rowCount(), $duration), '+1');
        
        
        $select->reset(Zend_Db_Select::WHERE);
        $select->where('RAND() < ?', $rate2);
        
        $sql = $adapter->insertFromSelect($select, $table, array_keys($columns));
        
        $start =  microtime(true);
        $stmt = $adapter->query($sql);
        $duration =  microtime(true) - $start;
        
        $this->log(sprintf('Generation of %s 2nd recipient events took %01.4fsec', $stmt->rowCount(), $duration), '+1');
    }
    
    
    
    public function _generateInboxEmail()
    {
        $table = $this->getResourceHelper()->getTable('mzax_emarketing/inbox_email');
        $adapter = $this->getWriteAdapter();
        $adapter->query("SET foreign_key_checks = 0;");
        $adapter->truncateTable($table);
        $adapter->query("SET foreign_key_checks = 1;");
    
        $columns = array(
            'recipient_id'  => 'recipient_id',
            'created_at'    => 'DATE_ADD(`sent_at`, INTERVAL FLOOR(30 + (RAND() * 1500*10)) MINUTE)',
            'is_parsed'     => new Zend_Db_Expr('1'),
            'type'          => $this->getRandomValueExpr('HB', 'SB', 'NB', 'AR')
        );
        
        $select = $adapter->select();
        $select->from($this->getResourceHelper()->getTable('mzax_emarketing/recipient'), null);
        $select->columns($columns);
        $select->where('RAND()<0.0005');
        
        $sql = $adapter->insertFromSelect($select, $table, array_keys($columns));
        
        $start =  microtime(true);
        $stmt = $adapter->query($sql);
        $duration =  microtime(true) - $start;
    
        $this->log(sprintf('Generation of %s inbox emails took %01.4fsec', $stmt->rowCount(), $duration), '+1');
   
    }
    
    
    
    
    
    /**
     * Create expression that convers a string value to
     * the value id previously insert into the enum table
     *
     * @param Zend_Db_Expr $valueExpr
     * @return Zend_Db_Expr
     */
    public function getRandomValueExpr($list)
    {
        if (!is_array($list)) {
            $list = func_get_args();
        }
        
        $adapter = $this->getWriteAdapter();
        
        $values = array();
        foreach ($list as $value) {
            $values[] = $adapter->quote($value);
        }
    
        $values = implode(', ', $values);
        $size = count($list);
    
        return new Zend_Db_Expr("ELT(FLOOR(1 + (RAND()*$size)), $values)");
    }
    
    
    
    
    
    
    public function getCampaigns()
    {
        $minStart   = '2004-01-01';
        $maxStart   = '2013-01-01';
        $minRunTime = 86400*100;
        $today      = time();
        
        $minStart = DateTime::createFromFormat(Varien_Date::DATE_PHP_FORMAT, $minStart)->getTimestamp();
        $maxStart = DateTime::createFromFormat(Varien_Date::DATE_PHP_FORMAT, $maxStart)->getTimestamp();
        
        if (!$this->_campaigns) {
            for($i = 1; $i <= self::NUMBER_OF_CAMPAIGNS; $i++) {
                $start = mt_rand($minStart, $maxStart);
                $end   = mt_rand($start + $minRunTime, $today);
                
                $this->_campaigns[$i] = array(
                    date(Varien_Date::DATE_PHP_FORMAT, $start),
                    date(Varien_Date::DATE_PHP_FORMAT, $end),
                    $this->getVariations($start, $end, (int) ($minRunTime*0.3))
                );
            }
        }
        return $this->_campaigns;
    }
    
    
    
    
    public function getVariations($start, $end, $minRunTime)
    {
        $count = rand(1,2);
        
        $variations = array();
        
        for($i = 0; $i <= $count; $i++) {
            $start = mt_rand($start, $start+$minRunTime);
            $end   = mt_rand($end - $minRunTime, $end);
        
            $variations[$i] = array(
                date(Varien_Date::DATE_PHP_FORMAT, $start),
                date(Varien_Date::DATE_PHP_FORMAT, $end),
            );
        }
        return $variations;
    }
    
    
    
    
    
    
    public function getTrackers() 
    {
        return array(4,5,7,8,9,12,14,18,20);
    }
    
    
    
    
    
    public function insertDimensionEnum()
    {
        $adapter = $this->getWriteAdapter();
        $table = $this->getResourceHelper()->getTable('mzax_emarketing/report_enum');
        $adapter->truncateTable($table);
        
        $inserts = array();
        foreach ($this->_dimensionsEnum as $dimension) {
            foreach ($dimension['values'] as $value) {
                $cells = array();
                $cells[] = $adapter->quote($dimension['id']);
                $cells[] = $adapter->quote($value['id']);
                $cells[] = $adapter->quote($dimension['label']);
                $cells[] = $adapter->quote($value['label']);
                $inserts[] = '(' . implode(', ', $cells) . ')';
            }
        }
        
        $columns = "`dimension_id`, `value_id`, `dimension`, `value`";
        
        $sql = implode(",\n", $inserts);
        $sql = "INSERT INTO `$table` ($columns) VALUES\n$sql";
        $adapter->query($sql);
    }
    
    
    public function aggregateDimensionReport()
    {
        $this->log(5)->log('Aggregate dimensions');
        
        $dimensions = array(
            'Hour'   => array('00', '01', '02', '05', '07', '08', '09', '10', '11', '12', '13', '19', '21', '23'),
            'Mail Client' => array('Apple Mail', 'KMail', 'Windows Live Mail', 'Microsoft Office Outlook', 'Opera Mail', 'Yahoo Mail'),
            'Device' => array('Desktop', 'Tablet', 'Mobile'),
            'OS' => array('Mac OS X', 'Windows', 'Unix'),
            'Country' => array('DE', 'AU', 'US', 'GB', 'IT', 'ES'),
            'Region-AU' => array('AU-NSW', 'AU-ACT', 'AU-NT' ,'AU-QLD', 'AU-SA', 'AU-VIC','AU-WA', 'AU-TAS'),
            'Region-DE' => array('DE-BW', 'DE-BY', 'DE-BE' ,'DE-BB', 'DE-HB', 'DE-HH','DE-HE', 'DE-RP'),
                
        );
        
        
        $this->beginnTempTable('mzax_emarketing/report_dimension');
        
        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        foreach ($this->getCampaigns() as $campaignId => $campaign) {
            
            
            $this->log("Aggregate dimensions for campaign: $campaignId", 1);
            
            foreach ($dimensions as $dimension => $values) {
            
                $this->log("Aggregate dimension: $dimension", 2);
                
                $sendings = rand(50, 100);
                $views    = rand(20, 30);
                $clicks   = rand(5, 15);
                $bounces  = rand(1, 2);
                $optouts  = rand(2, 6);
                
                
                foreach ($values as $value) {
                    
                    list($dimensionId, $valueId) = $this->registerDimensionValue($dimension, $value);
                    
                    $this->_insertData($campaign[0], $campaign[1], array(
                        'campaign_id'  => $campaignId,
                        'dimension_id' => $dimensionId,
                        'variation_id' => -1,
                        'value_id'     => $valueId,
                        'date'         => 'DATE()',
                        'sendings'     => "RANGE(0,$sendings)",
                        'views'        => "RANGE(0,$views)",
                        'clicks'       => "RANGE(0,$clicks)",
                        'bounces'      => "RANGE(0,$bounces)",
                        'optouts'      => "RANGE(0,$optouts)"
                    ));
                    
                    $m = 1/rand(2, 4);
                    
                    $sendings = (int) ($sendings * $m);
                    $views    = (int) ($views * $m);
                    $clicks   = (int) ($clicks * $m);
                    $bounces  = (int) ($bounces * $m);
                    $optouts  = (int) ($optouts * $m);
                    
                    foreach ($campaign[2] as $variationId => $range) {
                        $this->_insertData($range[0], $range[1], array(
                            'campaign_id'  => $campaignId,
                            'variation_id' => $variationId,
                            'dimension_id' => $dimensionId,
                            'value_id'     => $valueId,
                            'date'         => 'DATE()',
                            'sendings'     => "RANGE(0,$sendings)",
                            'views'        => "RANGE(0,$views)",
                            'clicks'       => "RANGE(0,$clicks)",
                            'bounces'      => "RANGE(0,$bounces)",
                            'optouts'      => "RANGE(0,$optouts)"
                        ));
                    }
                }
            }
        }
        
        $this->commitTempTable();
        
        
        
        
        $this->beginnTempTable('mzax_emarketing/report_dimension_conversion');
        
        foreach ($this->getTrackers() as $trackerId)
        {
            $this->log("Tracker: $trackerId", 1);
            foreach ($this->getCampaigns() as $campaignId => $campaign) 
            {
                $this->log("Aggregate dimensions convertion for campaign: $campaignId", 2);
                foreach ($dimensions as $dimension => $values) 
                {
                    $this->log("Aggregate dimension: $dimension", 3);
                    foreach ($values as $value) 
                    {
                        list($dimensionId, $valueId) = $this->registerDimensionValue($dimension, $value);
            
                        $this->_insertData($campaign[0], $campaign[1], array(
                            'campaign_id'  => $campaignId,
                            'variation_id' => -1,
                            'dimension_id' => $dimensionId,
                            'tracker_id'   => $trackerId,
                            'value_id'     => $valueId,
                            'date'         => 'DATE()',
                            'hits'         => 'RANGE(0,10)',
                            'revenue'      => 'RANGE(1000,20000,100)',
                        ));
            
                        foreach ($campaign[2] as $variationId => $range) {
                            $this->_insertData($range[0], $range[1], array(
                                'campaign_id'  => $campaignId,
                                'variation_id' => $variationId,
                                'tracker_id'   => $trackerId,
                                'dimension_id' => $dimensionId,
                                'value_id'     => $valueId,
                                'date'         => 'DATE()',
                                'hits'         => 'RANGE(0,5)',
                                'revenue'      => 'RANGE(1000,20000,100)',
                            ));
                        }
                    }
                }
            }
        }
        $this->commitTempTable();
        
        
        $this->insertDimensionEnum();
        
    }
    
    
    
    protected $_dimensionsEnum = array();
    
    public function registerDimensionValue($dimension, $value)
    {
        $dkey = trim(strtolower($dimension));
        $vkey = trim(strtolower($value));
        
        
        if (!isset($this->_dimensionsEnum[$dkey])) {
            $this->_dimensionsEnum[$dkey] = array(
                'id'     => count($this->_dimensionsEnum)+1,
                'label'  => $dimension,
                'values' => array()
            );
        }
        
        $values = &$this->_dimensionsEnum[$dkey]['values'];
        
        if (!isset($values[$vkey])) {
            $values[$vkey] = array(
                'id'    => count($values)+1,
                'label' => $value
            );
        }
        
        return array($this->_dimensionsEnum[$dkey]['id'], $values[$vkey]['id']);
    }


/*
    protected function insertDimensionReport($campaignId, $variationId, $dimension, $value, $data)
    {
        $adapter = $this->getWriteAdapter();
        $table = $this->getResourceHelper()->getTable('mzax_emarketing/report_dimension');
        
        list($dimensionId, $valueId) = $this->registerDimensionValue($dimension, $value);
        
        
        $sql = array();
        foreach ($data as $row) {
            $insert = array();
            $insert[] = (int) $campaignId;
            if ($variationId !== null) {
                $insert[] = (int) $variationId;
            }
            else {
                $insert[] = 'NULL';
            }
            $insert[] = (int) $dimensionId;
            $insert[] = (int) $valueId;
            
            foreach ($row as $col => $val) {
                $insert[] = $adapter->quote($val);
            }
            $sql[] = '(' . implode(', ', $insert) . ')';
        }
    
        $columns = "`campaign_id`, `variation_id`, `dimension_id`, `value_id`, `date`, `sendings`, `views`, `clicks`, `bounces`, `optouts`";
    
        $sql = implode(",\n", $sql);
        $sql = "INSERT INTO `$table` ($columns) VALUES\n$sql";
       // $adapter->query($sql);
    
        return;
    }
    
    */
    
    
    
    
    
    
    
    protected function aggregateReport()
    {
        $this->beginnTempTable('mzax_emarketing/report');
        
        $this->log("Aggregate Report Data");
        
        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        foreach ($this->getCampaigns() as $campaignId => $campaign) 
        {
            $this->log("Campaign: $campaignId", 1);
            $this->_insertData($campaign[0], $campaign[1], array(
                'campaign_id'  => $campaignId,
                'variation_id' => -1,
                'date'         => 'DATE()', 
                'sendings'     => 'RANGE(0,80)',
                'views'        => 'RANGE(0,30)',
                'clicks'       => 'RANGE(0,10)',
                'bounces'      => 'RANGE(0,2)',
                'optouts'      => 'RANGE(0,5)'
            ));
            
            foreach ($campaign[2] as $variationId => $range) 
            {
                $this->_insertData($range[0], $range[1], array(
                    'campaign_id'  => $campaignId,
                    'variation_id' => $variationId,
                    'date'         => 'DATE()',
                    'sendings'     => 'RANGE(0,25)',
                    'views'        => 'RANGE(0,15)',
                    'clicks'       => 'RANGE(0,4)',
                    'bounces'      => 'RANGE(0,1)',
                    'optouts'      => 'RANGE(0,2)'
                ));
            }
        }
        $this->commitTempTable();
        
        
        $this->beginnTempTable('mzax_emarketing/report_conversion');
        
        foreach ($this->getTrackers() as $trackerId) 
        {
            $this->log("Tracker: $trackerId", 1);
            foreach ($this->getCampaigns() as $campaignId => $campaign) 
            {
                $this->log("Campaign: $campaignId", 2);
                $this->_insertData($campaign[0], $campaign[1], array(
                    'campaign_id'  => $campaignId,
                    'variation_id' => -1,
                    'tracker_id'   => $trackerId,
                    'date'         => 'DATE()',
                    'hits'         => 'RANGE(0,10)',
                    'revenue'      => 'RANGE(1000,20000,100)',
                ));  
                foreach ($campaign[2] as $variationId => $range) 
                {
                    $this->_insertData($range[0], $range[1], array(
                        'campaign_id'  => $campaignId,
                        'variation_id' => $variationId,
                        'tracker_id'   => $trackerId,
                        'date'         => 'DATE()',
                        'hits'         => 'RANGE(0,10)',
                        'revenue'      => 'RANGE(1000,20000,100)',
                    ));
                }
            }
        }
        
        $this->commitTempTable();
        
        
        $this->calculateConversionRates();
        
    }
    
    
    

    
    
    
    protected function calculateConversionRates()
    {
        $adapter = $this->getWriteAdapter();
        $table = $this->getResourceHelper()->getTable('mzax_emarketing/report');
        
        $totalColumn = 'sendings';
        $rateColumns = array('view', 'click', 'bounce', 'optout');
        
        
        
        $columns = array(
            'campaign_id'  => 'result.campaign_id',
            'variation_id' => 'result.variation_id',
            'date'         => 'result.date',
        );
        
        foreach ($rateColumns as $key) {
            $columns[$key.'_rate'] = new Zend_Db_Expr("(SUM(`calc`.`{$key}s`)/SUM(`calc`.`$totalColumn`))*100");
        }
        
        $cond = array();
        $cond[] = '(`calc`.`date` <= `result`.`date`)';
        $cond[] = '(`calc`.`campaign_id` = `result`.`campaign_id`)';
        $cond[] = '(`calc`.`variation_id` = `result`.`variation_id`)';
        $cond = implode(' AND ', $cond);
                
        $select = $adapter->select();
        $select->from(array('result' => $table), null);
        $select->join(array('calc'   => $adapter->select()->from($table, '*')), $cond, $columns);
        $select->group(array('result.date', 'result.campaign_id' ,'result.variation_id'));
        
        $cond = array();
        $cond[] = '(`rate`.`date` = `report`.`date`)';
        $cond[] = '(`rate`.`campaign_id` = `report`.`campaign_id`)';
        $cond[] = '(`rate`.`variation_id` IS NULL AND `report`.`variation_id` IS NULL OR `rate`.`variation_id` = `report`.`variation_id`)';
        $cond = implode(' AND ', $cond);
        
        
        $assignments = array();
        foreach ($rateColumns as $key) {
            $assignments[] = "\t`report`.`{$key}_rate` = CONVERT(`rate`.`{$key}_rate`, DECIMAL(5,2))";
        }
        
        $updateSql = "UPDATE `$table` AS `report`\n";
        $updateSql.= "INNER JOIN($select) AS `rate` ON $cond\n";
        $updateSql.= "SET\n";
        $updateSql.= implode(", \n", $assignments);
        
        
        $start =  microtime(true);
        $adapter->query($updateSql);
        $duration =  microtime(true) - $start;
        
        $this->log(sprintf('calculateConversionRates() %01.4fsec', $duration), '0');
    }
    
    
    
    
    
    
    protected function _prepareDateRange(&$startDate, &$endDate)
    {
        if (!$endDate) {
            $endDate = new DateTime;
        }
        if (is_string($startDate)) {
            $startDate = new DateTime($startDate);
        }
        if (is_string($endDate)) {
            $endDate = new DateTime($endDate);
        }
    }

    
    
    /**
     *
     * @return Mzax_Emarketing_Model_Resource_Helper
     */
    protected function getResourceHelper()
    {
        return Mage::getResourceSingleton('mzax_emarketing/helper');
    }
    
    
    
    /**
     * Retrieve connection for read data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function getWriteAdapter()
    {
        return $this->getResourceHelper()->getWriteAdapter();
    }
    
    
    protected $_currentIndent = 0;
    
    protected function log($message, $indent = 0)
    {
        if (is_int($message)) {
            return $this->log(str_repeat("\n", $message), $indent);
        }
        if (is_int($indent)) {
            $this->_currentIndent = $indent;
        }
        elseif (is_string($indent)) {
            $indent = $this->_currentIndent + $indent;
        }
        echo str_repeat("    ", $indent) . $message . "\n";
        flush();
        return $this;
    }
    
    
    

    
    
    
    protected $_destinationTable;
    
    protected $_tempTableName = 'mzax_report_tmp';
    

    

    /**
     * Generate Temporary Timeline table
     *
     * @param string $startDate
     * @param string $endDate
     */
    protected function _generateEntityIds($limit = 5, $count = 10000)
    {
        $adapter = $this->getWriteAdapter();
        $adapter->query("DROP TABLE IF EXISTS `temp_entities`");
        $adapter->query("CREATE TABLE `temp_entities` (`entity_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY) /*ENGINE MEMORY*/");
        
        
        
        $inserts = array();
        while(--$count > -1) {
            $inserts[] = "(NULL)";
        }
        
        $inserts = implode(",\n", $inserts);
        $sql = "INSERT INTO `temp_entities` (`entity_id`) VALUES\n$inserts";
        $adapter->query($sql);
        
        $start =  microtime(true);
        $adapter->query($sql);
        $duration =  microtime(true) - $start;
        
        $this->log(sprintf("Insert start entites (%ssec)", $duration));
        
        
        while(--$limit > -1) {
            $sql = "INSERT INTO `temp_entities` (`entity_id`)\nSELECT NULL AS `entity_id` FROM `temp_entities`";
            $start =  microtime(true);
            $adapter->query($sql);
            $duration =  microtime(true) - $start;
            
            $this->log(sprintf("Insert select entites (%ssec)", $duration));
        }
        
        
        
    }
    
    
    
    
    
    
    /**
     * Generate Temporary Timeline table
     * 
     * @param string $startDate
     * @param string $endDate
     */
    protected function _generateTimeline($startDate = '2006-01-01', $endDate = null)
    {
        $this->_prepareDateRange($startDate, $endDate);
    
        $adapter = $this->getWriteAdapter();
        $adapter->query("DROP TABLE IF EXISTS `temp_range`");
        $adapter->query("CREATE TEMPORARY TABLE `temp_range` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `date` DATE NOT NULL UNIQUE KEY) ENGINE MEMORY");
    
        $data = array();
        $date = clone $startDate;
        $day  = new DateInterval('P1D');
    
        $inserts = array();
        do {
            $inserts[] = "('{$date->format("Y-m-d")}')";
        }
        while($date->add($day) <= $endDate);
    
        $inserts = implode(",\n", $inserts);
        $sql = "INSERT INTO `temp_range` (`date`) VALUES\n$inserts";
        $adapter->query($sql);
    }
    
    
    
    
    
    
    
    
    
    /**
     * Create Temp Table from specified Table
     * 
     * @param string $fromTable
     */
    protected function beginnTempTable($fromTable)
    {
        $adapter = $this->getWriteAdapter();
        $table = $this->getResourceHelper()->getTable($fromTable);
    
        $this->_destinationTable = $table;
    
        $adapter->query("DROP TEMPORARY TABLE IF EXISTS `$this->_tempTableName`");
        $adapter->query("CREATE TEMPORARY TABLE `$this->_tempTableName` SELECT * FROM `$table` WHERE 1=0");
    
        $this->log(4)->log("\n\n\nStart Preparing Data for $fromTable");
    }
    
    
    
    
    /**
     * Commit all insert data from temp table to destination table
     * 
     * @return void
     */
    protected function commitTempTable()
    {
        if ($this->_destinationTable) {
            $adapter = $this->getWriteAdapter();
            sleep(1);
            $start =  microtime(true);
            $adapter->query("LOCK TABLES `$this->_destinationTable` WRITE;");
            $adapter->truncateTable($this->_destinationTable);
            $adapter->query("INSERT INTO `$this->_destinationTable` SELECT * FROM `$this->_tempTableName`");
            $adapter->query("UNLOCK TABLES;");
            $adapter->query("DROP TEMPORARY TABLE IF EXISTS `$this->_tempTableName`");
        
            $duration = microtime(true) - $start;
            $this->log(sprintf('Commit table `%s` took %01.4fsec', $this->_destinationTable, $duration), '+0');
            
            $this->_destinationTable = null;
        }
    }
    
    
    
    
    
    
    
    /**
     * Generate temporary data for the current destination table
     * 
     * @param string $start
     * @param string $end
     * @param array $values
     */
    protected function _insertData($start, $end, array $values)
    {
        $adapter = $this->getWriteAdapter();
    
        $select = $adapter->select();
        $select->from('temp_range' , null);
        $select->where('`date` >= ?', $start);
        $select->where('`date` <= ?', $end);
    
        $null = new Zend_Db_Expr('NULL');
    
        $columns = array();
        foreach ($values as $key => $value) {
            if ($value instanceof Zend_Db_Expr) {
                $columns[$key] = $value;
            }
            else if ($value === null) {
                $columns[$key] = $null;
            }
            else if (preg_match('/RANGE\(\s*([0-9]+)\s*,\s*([0-9]+)\s*(?:,\s*([0-9]+))?\)/i', $value, $match)) {
                $min = $match[1];
                $scale = $match[2] - $min;
                
                if (isset($match[3])) {
                    $min /= $match[3];
                    $scale /= $match[3];
                }
                
                $columns[$key] = new Zend_Db_Expr("FLOOR({$min} + (RAND() * $scale))");
            }
            else if ($value === 'DATE()') {
                $columns[$key] = 'temp_range.date';
            }
            else {
                $columns[$key] = new Zend_Db_Expr($value);
            }
        }
    
        $select->columns($columns);
        
        $sql = $adapter->insertFromSelect($select, $this->_tempTableName, array_keys($columns));
         
        $start =  microtime(true);
        $stmt = $adapter->query($sql);
        $duration =  microtime(true) - $start;
        
        $this->log(sprintf('Generation of %s values took %01.4fsec', $stmt->rowCount(), $duration), '+1');
    }
    
    

    /**
     * Retrieve table name
     *
     * @param string $table
     * @param string $alias
     * @return string
     */
    protected function _getTable($table, $alias = null)
    {
        if ($table instanceof Zend_Db_Select) {
            return array($alias => $table);
        }
        $table = $this->getResourceHelper()->getTable($table);
        if ($alias) {
            return array($alias => $table);
        }
        return $table;
    }
    
    
}
