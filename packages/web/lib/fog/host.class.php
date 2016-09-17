<?php
/**
 * The host object (main item FOG deals with
 *
 * PHP version 5
 *
 * @category Host
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0.txt GPLv3
 * @link     https://fogproject.org
 */
/**
 * The host object (main item FOG deals with
 *
 * @category Host
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0.txt GPLv3
 * @link     https://fogproject.org
 */
class Host extends FOGController
{
    /**
     * The host table
     *
     * @var string
     */
    protected $databaseTable = 'hosts';
    /**
     * The Host table fields and common names
     *
     * @var array
     */
    protected $databaseFields = array(
        'id' => 'hostID',
        'name' => 'hostName',
        'description' => 'hostDesc',
        'ip' => 'hostIP',
        'imageID' => 'hostImage',
        'building' => 'hostBuilding',
        'createdTime' => 'hostCreateDate',
        'deployed' => 'hostLastDeploy',
        'createdBy' => 'hostCreateBy',
        'useAD' => 'hostUseAD',
        'ADDomain' => 'hostADDomain',
        'ADOU' => 'hostADOU',
        'ADUser' => 'hostADUser',
        'ADPass' => 'hostADPass',
        'ADPassLegacy' => 'hostADPassLegacy',
        'productKey' => 'hostProductKey',
        'printerLevel' => 'hostPrinterLevel',
        'kernelArgs' => 'hostKernelArgs',
        'kernel' => 'hostKernel',
        'kernelDevice' => 'hostDevice',
        'init' => 'hostInit',
        'pending' => 'hostPending',
        'pub_key' => 'hostPubKey',
        'sec_tok' => 'hostSecToken',
        'sec_time' => 'hostSecTime',
        'pingstatus' => 'hostPingCode',
        'biosexit' => 'hostExitBios',
        'efiexit' => 'hostExitEfi',
        'enforce' => 'hostEnforce',
    );
    /**
     * The required fields
     *
     * @var array
     */
    protected $databaseFieldsRequired = array(
        'name',
    );
    /**
     * Additional fields
     *
     * @var array
     */
    protected $additionalFields = array(
        'mac',
        'primac',
        'imagename',
        'additionalMACs',
        'pendingMACs',
        'groups',
        'groupsnotinme',
        'hostscreen',
        'hostalo',
        'optimalStorageNode',
        'printers',
        'printersnotinme',
        'snapins',
        'snapinsnotinme',
        'modules',
        'inventory',
        'task',
        'snapinjob',
        'users',
        'fingerprint',
        'powermanagementtasks',
    );
    /**
     * Database -> Class field relationships
     *
     * @var array
     */
    protected $databaseFieldClassRelationships = array(
        'MACAddressAssociation' => array(
            'hostID',
            'id',
            'primac',
            array('primary' => 1)
        ),
        'Image' => array(
            'id',
            'imageID',
            'imagename'
        ),
        'Inventory' => array(
            'hostID',
            'id',
            'inventory'
        ),
        'HostScreenSettings' => array(
            'hostID',
            'id',
            'hostscreen'
        ),
        'HostAutoLogout' => array(
            'hostID',
            'id',
            'hostalo'
        ),
    );
    /**
     * Display val storage
     *
     * @var array
     */
    private static $_hostscreen = array();
    /**
     * ALO time val
     *
     * @var int
     */
    private static $_hostalo = array();
    /**
     * Set value to key
     *
     * @param string $key   the key to set to
     * @param mixed  $value the value to set
     *
     * @throws Exception
     * @return object
     */
    public function set($key, $value)
    {
        $key = $this->key($key);
        switch ($key) {
            case 'mac':
                if (!($value instanceof MACAddress)) {
                    $value = self::getClass('MACAddress', $value);
                }
                break;
            case 'additionalMACs':
            case 'pendingMACs':
                $newValue = array_map(function (&$mac) {
                    return self::getClass('MACAddress', $mac);
                }, (array)$value);
                $value = (array)$newValue;
                break;
            case 'snapinjob':
                if (!($value instanceof SnapinJob)) {
                    $value = self::getClass('SnapinJob', $value);
                }
                break;
            case 'inventory':
                if (!($value instanceof Inventory)) {
                    $value = self::getClass('Inventory', $value);
                }
                break;
            case 'task':
                if (!($value instanceof Task)) {
                    $value = self::getClass('Task', $value);
                }
                break;
        }
        return parent::set($key, $value);
    }
    /**
     * Add value to key (array)
     *
     * @param string $key   the key to add to
     * @param mixed  $value the value to add
     *
     * @throws Exception
     * @return object
     */
    public function add($key, $value)
    {
        $key = $this->key($key);
        switch ($key) {
            case 'additionalMACs':
            case 'pendingMACs':
                if (!($value instanceof MACAddress)) {
                    $value = self::getClass('MACAddress', $value);
                }
                break;
        }
        return parent::add($key, $value);
    }
    /**
     * Removes the item from the database
     *
     * @param string $key the key to remove
     *
     * @throws Exception
     * @return object
     */
    public function destroy($key = 'id')
    {
        $find = array('hostID' => $this->get('id'));
        self::getClass('NodeFailureManager')
            ->destroy($find);
        self::getClass('ImagingLogManager')
            ->destroy($find);
        self::getClass('SnapinTaskManager')
            ->destroy(
                array(
                    'jobID' => self::getSubObjectIDs(
                        'SnapinJob',
                        $find,
                        'id'
                    )
                )
            );
        self::getClass('SnapinJobManager')
            ->destroy($find);
        self::getClass('TaskManager')
            ->destroy($find);
        self::getClass('ScheduledTaskManager')
            ->destroy($find);
        self::getClass('HostAutoLogoutManager')
            ->destroy($find);
        self::getClass('HostScreenSettingsManager')
            ->destroy($find);
        self::getClass('GroupAssociationManager')
            ->destroy($find);
        self::getClass('SnapinAssociationManager')
            ->destroy($find);
        self::getClass('PrinterAssociationManager')
            ->destroy($find);
        self::getClass('ModuleAssociationManager')
            ->destroy($find);
        self::getClass('GreenFogManager')
            ->destroy($find);
        self::getClass('InventoryManager')
            ->destroy($find);
        self::getClass('UserTrackingManager')
            ->destroy($find);
        self::getClass('MACAddressAssociationManager')
            ->destroy($find);
        self::getClass('PowerManagementManager')
            ->destroy($find);
        return parent::destroy($field);
    }
    /**
     * Returns Valid MACs
     *
     * @param array $macs the array of macs
     * @param array $arr  the array to define
     *
     * @return array
     */
    private static function _retValidMacs($macs, &$arr)
    {
        $addMacs = array();
        foreach ((array)$macs as &$mac) {
            if (!($mac instanceof MACAddress)) {
                $mac = new MACAddress($mac);
            }
            if (!$mac->isValid()) {
                continue;
            }
            $addMacs[] = $mac->__toString();
            unset($mac);
        }
        return $arr = $addMacs;
    }
    /**
     * Stores data into the database
     *
     * @return bool|object
     */
    public function save()
    {
        parent::save();
        if ($this->isLoaded('mac')) {
            if (!$this->get('mac')->isValid()) {
                throw new Exception(self::$foglang['InvalidMAC']);
            }
            $RealPriMAC = $this->get('mac')->__toString();
            $CurrPriMAC = self::getSubObjectIDs(
                'MACAddressAssociation',
                array(
                    'hostID' => $this->get('id'),
                    'primary' => 1
                ),
                'mac'
            );
            if (count($CurrPriMAC) === 1
                && $CurrPriMAC[0] != $RealPriMAC
            ) {
                self::getClass('MACAddressAssociationManager')
                    ->update(
                        array(
                            'mac' => $CurrPriMAC[0],
                            'hostID' => $this->get('id'),
                            'primary' => 1
                        ),
                        '',
                        array('primary' => 0)
                    );
            }
            $HostWithMAC = array_diff(
                (array)$this->get('id'),
                (array)self::getSubObjectIDs(
                    'MACAddressAssociation',
                    array('mac' => $RealPriMAC),
                    'hostID'
                )
            );
            if (count($HostWithMAC)
                && !in_array($this->get('id'), (array)$HostWithMAC)
            ) {
                throw new Exception(_('This MAC Belongs to another host'));
            }
            $DBPriMACs = self::getSubObjectIDs(
                'MACAddressAssociation',
                array(
                    'hostID' => $this->get('id'),
                    'primary' => 1
                ),
                'mac'
            );
            $RemoveMAC = array_diff(
                (array)$RealPriMAC,
                (array)$DBPriMACs
            );
            if (count($RemoveMAC)) {
                self::getClass('MACAddressAssociationManager')
                    ->destroy(
                        array('mac' => $RemoveMAC)
                    );
                unset($RemoveMAC);
                $DBPriMACs = self::getSubObjectIDs(
                    'MACAddressAssociation',
                    array(
                        'hostID' => $this->get('id'),
                        'primary' => 1
                    ),
                    'mac'
                );
            }
            if (!in_array($RealPriMAC, $DBPriMACs)) {
                self::getClass('MACAddressAssociation')
                    ->set('hostID', $this->get('id'))
                    ->set('mac', $RealPriMAC)
                    ->set('primary', 1)
                    ->save();
            }
            unset(
                $DBPriMACs,
                $RealPriMAC,
                $RemoveMAC,
                $HostWithMAC
            );
        }
        if ($this->isLoaded('additionalMACs')) {
            self::_retValidMacs(
                $this->get('additionalMACs'),
                $addMacs
            );
            $RealAddMACs = array_filter($addMacs);
            unset($addMacs);
            $RealAddMACs = array_unique($RealAddMACs);
            $RealAddMACs = array_filter($RealAddMACs);
            $DBPriMACs = self::getSubObjectIDs(
                'MACAddressAssociation',
                array('primary' => 1),
                'mac'
            );
            foreach ((array)$DBPriMACs as &$mac) {
                if ($this->arrayStrpos($mac, $RealAddMACs) !== false) {
                    throw new Exception(
                        _('Cannot add Primary mac as additional mac')
                    );
                }
                unset($mac);
            }
            unset($DBPriMACs);
            $PreOwnedMACs = self::getSubObjectIDs(
                'MACAddressAssociation',
                array(
                    'hostID' => $this->get('id'),
                    'pending' => 1
                ),
                'mac',
                true
            );
            $RealAddMACs = array_diff(
                (array)$RealAddMACs,
                (array)$PreOwnedMACs
            );
            unset($PreOwnedMACs);
            $DBAddMACs = self::getSubObjectIDs(
                'MACAddressAssociation',
                array(
                    'hostID' => $this->get('id'),
                    'primary' => 0,
                    'pending' => 0
                ),
                'mac'
            );
            $RemoveAddMAC = array_diff(
                (array)$DBAddMACs,
                (array)$RealAddMACs
            );
            if (count($RemoveAddMAC)) {
                self::getClass('MACAddressAssociationManager')
                    ->destroy(
                        array(
                            'hostID' => $this->get('id'),
                            'mac' => $RemoveAddMAC
                        )
                    );
                $DBAddMACs = self::getSubObjectIDs(
                    'MACAddressAssociation',
                    array(
                        'hostID' => $this->get('id'),
                        'primary' => 0,
                        'pending' => 0,
                        'mac'
                    )
                );
                unset($RemoveAddMAC);
            }
            $insert_fields = array(
                'hostID',
                'mac',
                'primary',
                'pending'
            );
            $insert_values = array();
            $RealAddMACs = array_diff(
                (array)$RealAddMACs,
                (array)$DBAddMACs
            );
            foreach ((array)$RealAddMACs as $index => &$mac) {
                $insert_values[] = array(
                    $this->get('id'),
                    $mac,
                    0,
                    0
                );
                unset($mac);
            }
            if (count($insert_values) > 0) {
                self::getClass('MACAddressAssociationManager')
                    ->insertBatch(
                        $insert_fields,
                        $insert_values
                    );
            }
            unset(
                $DBAddMACs,
                $RealAddMACs,
                $RemoveAddMAC
            );
        }
        if ($this->isLoaded('pendingMACs')) {
            self::_retValidMacs($this->get('pendingMACs'), $pendMacs);
            $RealPendMACs = array_filter($pendMacs);
            unset($pendMacs);
            $RealPendMACs = array_unique($RealPendMACs);
            $RealPendMACs = array_filter($RealPendMACs);
            $DBPriMACs = self::getSubObjectIDs(
                'MACAddressAssociation',
                array('primary' => 1),
                'mac'
            );
            foreach ((array)$DBPriMACs as &$mac) {
                if ($this->arrayStrpos($mac, $RealPendMACs)) {
                    throw new Exception(
                        _('Cannot add a pre-existing primary mac')
                    );
                }
                unset($mac);
            }
            unset($DBPriMACs);
            $PreOwnedMACs = self::getSubObjectIDs(
                'MACAddressAssociation',
                array(
                    'hostID' => $this->get('id'),
                    'pending' => 0,
                    'mac',
                    true
                ),
                'mac',
                true
            );
            $RealPendMACs = array_diff(
                (array)$RealPendMACs,
                (array)$PreOwnedMACs
            );
            unset($PreOwnedMACs);
            $DBPendMACs = self::getSubObjectIDs(
                'MACAddressAssociation',
                array(
                    'hostID' => $this->get('id'),
                    'primary' => 0,
                    'pending' => 1,
                ),
                'mac'
            );
            $RemovePendMAC = array_diff(
                (array)$DBPendMACs,
                (array)$RealPendMACs
            );
            if (count($RemovePendMAC)) {
                self::getClass('MACAddressAssociationManager')
                    ->destroy(
                        array(
                            'hostID' => $this->get('id'),
                            'mac' => $RemovePendMAC
                        )
                    );
                $DBPendMACs = self::getSubObjectIDs(
                    'MACAddressAssociation',
                    array(
                        'primary' => 0,
                        'pending' => 1,
                    ),
                    'mac'
                );
                unset($RemovePendMAC);
            }
            $insert_fields = array(
                'hostID',
                'mac',
                'primary',
                'pending'
            );
            $insert_values = array();
            $RealPendMACs = array_diff(
                (array)$RealPendMACs,
                (array)$DBPendMACs
            );
            foreach ((array)$RealPendMACs as &$mac) {
                $insert_values[] = array(
                    $this->get('id'),
                    $mac,
                    0,
                    1
                );
                unset($mac);
            }
            if (count($insert_values) > 0) {
                self::getClass('MACAddressAssociationManager')
                    ->insertBatch(
                        $insert_fields,
                        $insert_values
                    );
            }
            unset(
                $DBPendMACs,
                $RealPendMACs,
                $RemovePendMAC
            );
        }
        if ($this->isLoaded('modules')) {
            $DBModuleIDs = self::getSubObjectIDs(
                'ModuleAssociation',
                array('hostID' => $this->get('id')),
                'moduleID'
            );
            $ValidModuleIDs = self::getSubObjectIDs('Module');
            $notValid = array_diff(
                (array)$DBModuleIDs,
                (array)$ValidModuleIDs
            );
            if (count($notValid)) {
                self::getClass('ModuleAssociationManager')
                    ->destroy(array('moduleID' => $notValid));
            }
            unset($ValidModuleIDs, $DBModuleIDs);
            $DBModuleIDs = self::getSubObjectIDs(
                'ModuleAssociation',
                array('hostID' => $this->get('id')),
                'moduleID'
            );
            $RemoveModuleIDs = array_diff(
                (array)$DBModuleIDs,
                (array)$this->get('modules')
            );
            if (count($RemoveModuleIDs)) {
                self::getClass('ModuleAssociationManager')->destroy(
                    array(
                        'moduleID' => $RemoveModuleIDs,
                        'hostID'=>$this->get('id')
                    )
                );
                $DBModuleIDs = self::getSubObjectIDs(
                    'ModuleAssociation',
                    array('hostID' => $this->get('id')),
                    'moduleID'
                );
                unset($RemoveModuleIDs);
            }
            $moduleName = $this->getGlobalModuleStatus();
            $insert_fields = array(
                'hostID',
                'moduleID',
                'state'
            );
            $insert_values = array();
            $DBModuleIDs = array_diff(
                (array)$this->get('modules'),
                (array)$DBModuleIDs
            );
            foreach ((array)$DBModuleIDs as &$moduleID) {
                $insert_values[] = array(
                    $this->get('id'),
                    $moduleID,
                    1
                );
                unset($moduleID);
            }
            if (count($insert_values)) {
                self::getClass('ModuleAssociationManager')
                    ->insertBatch(
                        $insert_fields,
                        $insert_values
                    );
            }
            unset($DBModuleIDs, $RemoveModuleIDs, $moduleName);
        }
        if ($this->isLoaded('printers')) {
            $DBPrinterIDs = self::getSubObjectIDs(
                'PrinterAssociation',
                array('hostID' => $this->get('id')),
                'printerID'
            );
            $ValidPrinterIDs = self::getSubObjectIDs('Printer');
            $notValid = array_diff(
                (array)$DBPrinterIDs,
                (array)$ValidPrinterIDs
            );
            if (count($notValid)) {
                self::getClass('PrinterAssociationManager')
                    ->destroy(array('printerID' => $notValid));
            }
            unset($ValidPrinterIDs, $DBPrinterIDs);
            $DBPrinterIDs = self::getSubObjectIDs(
                'PrinterAssociation',
                array('hostID' => $this->get('id')),
                'printerID'
            );
            $RemovePrinterIDs = array_diff(
                (array)$DBPrinterIDs,
                (array)$this->get('printers')
            );
            if (count($RemovePrinterIDs)) {
                self::getClass('PrinterAssociationManager')
                    ->destroy(
                        array(
                            'hostID' => $this->get('id'),
                            'printerID' => $RemovePrinterIDs
                        )
                    );
                $DBPrinterIDs = self::getSubObjectIDs(
                    'PrinterAssociation',
                    array('hostID' => $this->get('id')),
                    'printerID'
                );
                unset($RemovePrinterIDs);
            }
            $insert_fields = array(
                'hostID',
                'printerID'
            );
            $insert_values = array();
            $DBPrinterIDs = array_diff(
                (array)$this->get('printers'),
                (array)$DBPrinterIDs
            );
            foreach ((array)$DBPrinterIDs as &$printerID) {
                $insert_values[] = array(
                    $this->get('id'),
                    $printerID
                );
                unset($printerID);
            }
            if (count($insert_values) > 0) {
                self::getClass('PrinterAssociationManager')
                    ->insertBatch(
                        $insert_fields,
                        $insert_values
                    );
            }
            unset($DBPrinterIDs, $RemovePrinterIDs);
        }
        if ($this->isLoaded('powermanagementtasks')) {
            $DBPowerManagementIDs = self::getSubObjectIDs(
                'PowerManagement',
                array('hostID'=>$this->get('id'))
            );
            $RemovePowerManagementIDs = array_diff(
                (array)$DBPowerManagementIDs,
                (array)$this->get('powermanagementtasks')
            );
            if (count($RemovePowerManagementIDs)) {
                self::getClass('PowerManagementManager')
                    ->destroy(
                        array(
                            'hostID' => $this->get('id'),
                            'id' => $RemovePowerManagementIDs
                        )
                    );
                $DBPowerManagementIDs = self::getSubObjectIDs(
                    'PowerManagement',
                    array('hostID' => $this->get('id'))
                );
                unset($RemovePowerManagementIDs);
            }
            $objNeeded = false;
            unset($DBPowerManagementIDs, $RemovePowerManagementIDs);
        }
        if ($this->isLoaded('snapins')) {
            $DBSnapinIDs = self::getSubObjectIDs(
                'SnapinAssociation',
                array('hostID' => $this->get('id')),
                'snapinID'
            );
            $ValidSnapinIDs = self::getSubObjectIDs('Snapin');
            $notValid = array_diff(
                (array)$DBSnapinIDs,
                (array)$ValidSnapinIDs
            );
            if (count($notValid)) {
                self::getClass('SnapinAssociationManager')
                    ->destroy(array('snapinID' => $notValid));
            }
            unset($ValidSnapinIDs, $DBSnapinIDs);
            $DBSnapinIDs = self::getSubObjectIDs(
                'SnapinAssociation',
                array('hostID' => $this->get('id')),
                'snapinID'
            );
            $RemoveSnapinIDs = array_diff(
                (array)$DBSnapinIDs,
                (array)$this->get('snapins')
            );
            if (count($RemoveSnapinIDs)) {
                self::getClass('SnapinAssociationManager')
                    ->destroy(
                        array(
                            'hostID' => $this->get('id'),
                            'snapinID' => $RemoveSnapinIDs
                        )
                    );
                $DBSnapinIDs = self::getSubObjectIDs(
                    'SnapinAssociation',
                    array('hostID' => $this->get('id')),
                    'snapinID'
                );
                unset($RemoveSnapinIDs);
            }
            $insert_fields = array(
                'hostID',
                'snapinID'
            );
            $insert_values = array();
            $DBSnapinIDs = array_diff(
                (array)$this->get('snapins'),
                (array)$DBSnapinIDs
            );
            foreach ((array)$DBSnapinIDs as &$snapinID) {
                $insert_values[] = array(
                    $this->get('id'),
                    $snapinID
                );
                unset($snapinID);
            }
            if (count($insert_values) > 0) {
                self::getClass('SnapinAssociationManager')
                    ->insertBatch(
                        $insert_fields,
                        $insert_values
                    );
            }
            unset($DBSnapinIDs, $RemoveSnapinIDs);
        }
        if ($this->isLoaded('groups')) {
            $DBGroupIDs = self::getSubObjectIDs(
                'GroupAssociation',
                array('hostID' => $this->get('id')),
                'groupID'
            );
            $ValidGroupIDs = self::getSubObjectIDs('Group');
            $notValid = array_diff(
                (array)$DBGroupIDs,
                (array)$ValidGroupIDs
            );
            if (count($notValid)) {
                self::getClass('GroupAssociationManager')
                    ->destroy(array('groupID' => $notValid));
            }
            unset($ValidGroupIDs, $DBGroupIDs);
            $DBSnapinIDs = self::getSubObjectIDs(
                'GroupAssociation',
                array('hostID' => $this->get('id')),
                'groupID'
            );
            $RemoveGroupIDs = array_diff(
                (array)$DBGroupIDs,
                (array)$this->get('groups')
            );
            if (count($RemoveGroupIDs)) {
                self::getClass('GroupAssociationManager')
                    ->destroy(
                        array(
                            'hostID' => $this->get('id'),
                            'groupID' => $RemoveGroupIDs
                        )
                    );
                $DBGroupIDs = self::getSubObjectIDs(
                    'GroupAssociation',
                    array('hostID' => $this->get('id')),
                    'groupID'
                );
                unset($RemoveGroupIDs);
            }
            $insert_fields = array(
                'hostID',
                'groupID'
            );
            $insert_values = array();
            $DBGroupIDs = array_diff(
                (array)$this->get('groups'),
                (array)$DBGroupIDs
            );
            foreach ((array)$DBGroupIDs as &$groupID) {
                $insert_values[] = array(
                    $this->get('id'),
                    $groupID
                );
                unset($groupID);
            }
            if (count($insert_values) > 0) {
                self::getClass('GroupAssociationManager')
                    ->insertBatch(
                        $insert_fields,
                        $insert_values
                    );
            }
            unset($DBGroupIDs, $RemoveGroupIDs);
        }
        return $this;
    }
    /**
     * Defines if the host is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return parent::isValid() && $this->isHostnameSafe();
    }
    /**
     * Tells us if the hostname is safe to use
     *
     * @param string $hostname the hostname to test
     *
     * @return bool
     */
    public function isHostnameSafe($hostname = '')
    {
        if (empty($hostname)) {
            $hostname = $this->get('name');
        }
        $pattern = '/^[\\w!@#$%^()\\-\'{}\\.~]{1,15}$/';
        return (bool)preg_match($pattern, $hostname);
    }
    /**
     * Returns if the printer is the default
     *
     * @param int $printerid the printer id to test
     *
     * @return bool
     */
    public function getDefault($printerid)
    {
        return (bool)self::getClass('PrinterAssociationManager')
            ->count(
                array(
                    'hostID' => $this->get('id'),
                    'printerID' => $printerid,
                    'isDefault' => 1
                )
            );
    }
    /**
     * Updates the default printer
     *
     * @param int   $printerid the printer id to update
     * @param mixed $onoff     whether to enable or disable
     *
     * @return object
     */
    public function updateDefault($printerid, $onoff)
    {
        self::getClass('PrinterAssociationManager')
            ->update(
                array(
                    'printerID' => $this->get('printers'),
                    'hostID' => $this->get('id')
                ),
                '',
                array('isDefault' => 0)
            );
        self::getClass('PrinterAssociationManager')
            ->update(
                array(
                    'printerID' => $printerid,
                    'hostID' => $this->get('id')
                ),
                '',
                array('isDefault' => $onoff)
            );
        return $this;
    }
    /**
     * Sets display vals for the host
     *
     * @return void
     */
    private function _setDispVals()
    {
        if (count(self::$_hostscreen)) {
            return;
        }
        if (!$this->get('hostscreen')->isValid()) {
            list(
                $refresh,
                $width,
                $height
            ) = self::getSubObjectIDs(
                'Service',
                array(
                    'name' => array(
                        'FOG_CLIENT_DISPLAYMANAGER_R',
                        'FOG_CLIENT_DISPLAYMANAGER_X',
                        'FOG_CLIENT_DISPLAYMANAGER_Y'
                    )
                ),
                'value',
                false,
                'AND',
                'name',
                false,
                false
            );
        } else {
            $refresh = $this->get('hostscreen')->get('refresh');
            $width = $this->get('hostscreen')->get('width');
            $height = $this->get('hostscreen')->get('height');
        }
        self::$_hostscreen = array(
            'refresh' => $refresh,
            'width' => $width,
            'height' => $height
        );
    }
    /**
     * Gets the display values
     *
     * @param string $key the key to get
     *
     * @return mixed
     */
    public function getDispVals($key = '')
    {
        $this->_setDispVals();
        return self::$_hostscreen[$key];
    }
    /**
     * Sets the display values
     *
     * @param mixed $x the width
     * @param mixed $y the height
     * @param mixed $r the refresh
     *
     * @return object
     */
    public function setDisp($x, $y, $r)
    {
        if (!$this->get('hostscreen')->isValid()) {
            $this->get('hostscreen')
                ->set('hostID', $this->get('id'));
        }
        $this->get('hostscreen')
            ->set('width', $x)
            ->set('height', $y)
            ->set('refresh', $r)
            ->save();
        return $this;
    }
    /**
     * Sets this hosts alo time (or default to global if needed
     *
     * @return void
     */
    private function _setAlo()
    {
        if (!empty(self::$_hostalo)) {
            return;
        }
        if (!$this->get('hostalo')->isValid()) {
            self::$_hostalo = self::getSetting('FOG_CLIENT_AUTOLOGOFF_MIN');
        } else {
            self::$_hostalo = $this->get('hostalo')->get('time');
        }
        return;
    }
    /**
     * Gets the auto logout time
     *
     * @return int
     */
    public function getAlo()
    {
        $this->_setAlo();
        return self::$_hostalo;
    }
    /**
     * Sets the auto logout time
     *
     * @param int $time the time to set
     *
     * @return object
     */
    public function setAlo($time)
    {
        if (!$this->get('hostalo')->isValid()) {
            $this->get('hostalo')
                ->set('hostID', $this->get('id'));
        }
        $this->get('hostalo')
            ->set('time', $time)
            ->save();
        return $this;
    }
    /**
     * Loads the mac additional field
     *
     * @return void
     */
    protected function loadMac()
    {
        $mac = new MACAddress($this->get('primac'));
        $this->set('mac', $mac);
    }
    /**
     * Loads any additional macs
     *
     * @return void
     */
    protected function loadAdditionalMACs()
    {
        $macs = self::getSubObjectIDs(
            'MACAddressAssociation',
            array(
                'hostID' => $this->get('id'),
                'primary' => 0,
                'pending' => 0,
            ),
            'mac'
        );
        $this->set('additionalMACs', $macs);
    }
    /**
     * Loads any pending macs
     *
     * @return void
     */
    protected function loadPendingMACs()
    {
        $macs = self::getSubObjectIDs(
            'MACAddressAssociation',
            array(
                'hostID' => $this->get('id'),
                'primary' => 0,
                'pending' => 1,
            ),
            'mac'
        );
        $this->set('pendingMACs', $macs);
    }
    /**
     * Loads any groups this host is in
     *
     * @return void
     */
    protected function loadGroups()
    {
        $groups = self::getSubObjectIDs(
            'GroupAssociation',
            array('hostID' => $this->get('id')),
            'groupID'
        );
        $groups = self::getSubObjectIDs(
            'Group',
            array('id' => $groups)
        );
        $this->set('groups', $groups);
    }
    /**
     * Loads any groups this host is not in
     *
     * @return void
     */
    protected function loadGroupsnotinme()
    {
        $find = array('id' => $this->get('groups'));
        $groups = self::getSubObjectIDs(
            'Group',
            $find,
            'id',
            true
        );
        $this->set('groupsnotinme', $groups);
    }
    /**
     * Loads any printers those host has
     *
     * @return void
     */
    protected function loadPrinters()
    {
        $printers = self::getSubObjectIDs(
            'PrinterAssociation',
            array('hostID' => $this->get('id')),
            'printerID'
        );
        $printers = self::getSubObjectIDs(
            'Printer',
            array('id' => $printers)
        );
        $this->set('printers', $printers);
    }
    /**
     * Loads any printers this host does not have
     *
     * @return void
     */
    protected function loadPrintersnotinme()
    {
        $find = array('id' => $this->get('printers'));
        $printers = self::getSubObjectIDs(
            'Printer',
            $find,
            'id',
            true
        );
        $this->set('printersnotinme', $printers);
        unset($find);
    }
    /**
     * Loads any snapins this host has
     *
     * @return void
     */
    protected function loadSnapins()
    {
        $snapins = self::getSubObjectIDs(
            'SnapinAssociation',
            array('hostID' => $this->get('id')),
            'snapinID'
        );
        $groups = self::getSubObjectIDs(
            'Snapin',
            array('id' => $snapins)
        );
        $this->set('snapins', $snapins);
    }
    /**
     * Loads any snapins this host does not have
     *
     * @return void
     */
    protected function loadSnapinsnotinme()
    {
        $find = array('id' => $this->get('snapins'));
        $groups = self::getSubObjectIDs(
            'Snapin',
            $find,
            'id',
            true
        );
        $this->set('snapinsnotinme', $groups);
    }
    /**
     * Loads any modules this host has
     *
     * @return void
     */
    protected function loadModules()
    {
        $modules = self::getSubObjectIDs(
            'ModuleAssociation',
            array('hostID' => $this->get('id')),
            'moduleID'
        );
        $modules = self::getSubObjectIDs(
            'Module',
            array('id' => $modules)
        );
        $this->set('modules', $modules);
    }
    /**
     * Loads any powermanagement tasks this host has
     *
     * @return void
     */
    protected function loadPowermanagementtasks()
    {
        $pms = self::getSubObjectIDs(
            'PowerManagement',
            array('hostID' => $this->get('id'))
        );
        $this->set('powermanagementtasks', $pms);
    }
    /**
     * Loads any users have logged in
     *
     * @return void
     */
    protected function loadUsers()
    {
        $users = self::getSubObjectIDs(
            'UserTracking',
            array('hostID' => $this->get('id'))
        );
        $this->set('users', $users);
    }
    protected function loadSnapinjob()
    {
        $this->set('snapinjob', @max(self::getSubObjectIDs('SnapinJob', array('stateID'=>array_merge($this->getQueuedStates(), (array)$this->getProgressState()), 'hostID'=>$this->get('id')), 'id')));
    }
    protected function loadTask()
    {
        $find['hostID'] = $this->get('id');
        $find['stateID'] = array_merge($this->getQueuedStates(), (array)$this->getProgressState());
        if (in_array($_REQUEST['type'], array('up', 'down'))) {
            $find['typeID'] = ($_REQUEST['type'] == 'up' ? array(2, 16) : array(1, 8, 15, 17, 24));
        }
        $this->set('task', @max(self::getSubObjectIDs('Task', $find, 'id')));
        unset($find);
    }
    protected function loadOptimalStorageNode()
    {
        $this->set('optimalStorageNode', self::getClass('Image', $this->get('imageID'))->getStorageGroup()->getOptimalStorageNode($this->get('imageID')));
    }
    public function getActiveTaskCount()
    {
        return self::getClass('TaskManager')->count(array('stateID'=>array_merge($this->getQueuedStates(), (array)$this->getProgressState()), 'hostID'=>$this->get('id')));
    }
    public function isValidToImage()
    {
        return ($this->getImage()->isValid() && $this->getOS()->isValid() && $this->getImage()->getStorageGroup()->isValid() && $this->getImage()->getStorageGroup()->getStorageNode()->isValid());
    }
    public function getOptimalStorageNode()
    {
        return $this->get('optimalStorageNode');
    }
    public function checkIfExist($taskTypeID)
    {
        $TaskType = self::getClass('TaskType', $taskTypeID);
        $isCapture = $TaskType->isCapture();
        $Image = $this->getImage();
        $StorageGroup = null;
        $StorageNode = null;
        self::$HookManager->processEvent('HOST_NEW_SETTINGS', array('Host'=>&$this, 'StorageNode'=>&$StorageNode, 'StorageGroup'=>&$StorageGroup, 'TaskType'=>&$TaskType));
        if (!$StorageGroup || !$StorageGroup->isValid()) {
            $StorageGroup = $Image->getStorageGroup();
        }
        if (!$StorageNode || !$StorageNode->isValid()) {
            $StorageNode = $StorageGroup->getMasterStorageNode();
        }
        if (!$StorageGroup || !$StorageGroup->isValid()) {
            throw new Exception(_('No Storage Group found for this image'));
        }
        if (!$StorageNode || !$StorageNode->isValid()) {
            throw new Exception(_('No Storage Node found for this image'));
        }
        if (!in_array($TaskType->get('id'), array(1, 8, 15, 17, 24))) {
            return true;
        }
        if (!in_array($Image->get('id'), $StorageNode->get('images'))) {
            throw new Exception(sprintf('%s: %s', _('Image not found on node'), $StorageNode->get('name')));
            return false;
        }
        return true;
    }
    /**
     * Creates the tasking so I don't have to keep typing it in for each element.
     *
     * @param string $taskName    the name to assign to the tasking
     * @param int    $taskTypeID  the task type id to set the tasking
     * @param string $username    the username to associate with the tasking
     * @param int    $groupID     the Storage Group ID to associate with
     * @param int    $memID       the Storage Node ID to associate with
     * @param bool   $imagingTask if the task is an imaging type
     * @param bool   $shutdown    if the task is to be shutdown once completed
     * @param string $passreset   if the task is a password reset task
     * @param bool   $debug       if the task is a debug task
     * @param bool   $wol         if the task is to wol
     *
     * @return object
     */
    private function _createTasking(
        $taskName,
        $taskTypeID,
        $username,
        $groupID,
        $memID,
        $imagingTask = true,
        $shutdown = false,
        $passreset = false,
        $debug = false,
        $wol = false
    ) {
        $Task = self::getClass('Task')
            ->set('name', $taskName)
            ->set('createdBy', $username)
            ->set('hostID', $this->get('id'))
            ->set('isForced', 0)
            ->set('stateID', $this->getQueuedState())
            ->set('typeID', $taskTypeID)
            ->set('NFSGroupID', $groupID)
            ->set('NFSMemberID', $memID)
            ->set('wol', (string)intval($wol));
        if ($imagingTask) {
            $Task->set('imageID', $this->getImage()->get('id'));
        }
        if ($shutdown) {
            $Task->set('shutdown', $shutdown);
        }
        if ($debug) {
            $Task->set('isDebug', $debug);
        }
        if ($passreset) {
            $Task->set('passreset', $passreset);
        }
        return $Task;
    }
    private function cancelJobsSnapinsForHost()
    {
        $SnapinJobs = self::getSubObjectIDs('SnapinJob', array('hostID'=>$this->get('id'), 'stateID'=>array_merge($this->getQueuedStates(), (array)$this->getProgressState())));
        self::getClass('SnapinTaskManager')->update(array('jobID'=>$SnapinJobs, 'stateID'=>array_merge($this->getQueuedStates(), (array)$this->getProgressState())), '', array('return'=>-9999, 'details'=>_('Cancelled due to new tasking.'), 'stateID'=>$this->getCancelledState()));
        self::getClass('SnapinJobManager')->update(array('id'=>$SnapinJobs), '', array('stateID'=>$this->getCancelledState()));
        $AllTasks = self::getSubObjectIDs('Task', array('stateID'=>array_merge($this->getQueuedStates(), (array)$this->getProgressState()), 'hostID'=>$this->get('id')));
        $MyTask = $this->get('task')->get('id');
        self::getClass('TaskManager')->update(array('id'=>array_diff((array)$AllTasks, (array)$MyTask)), '', array('stateID'=>$this->getCancelledState()));
    }
    private function createSnapinTasking($snapin = -1, $error = false, $Task = false)
    {
        try {
            if (count($this->get('snapins')) < 1) {
                throw new Exception(_('No snapins associated with this host'));
            }
            $SnapinJob = $this->get('snapinjob');
            if (!$SnapinJob->isValid()) {
                $SnapinJob
                    ->set('hostID', $this->get('id'))
                    ->set('stateID', $this->getQueuedState())
                    ->set('createdTime', self::niceDate()->format('Y-m-d H:i:s'));
                if (!$SnapinJob->save()) {
                    throw new Exception(_('Failed to create Snapin Job'));
                }
            }
            $insert_fields = array('jobID','stateID','snapinID');
            $insert_values = array_map(function (&$snapinID) use ($SnapinJob) {
                return array($SnapinJob->get('id'), $this->getQueuedState(), $snapinID);
            }, $snapin == -1 ? (array)$this->get('snapins') : (array)$snapin);
            if (count($insert_values) > 0) {
                self::getClass('SnapinTaskManager')->insertBatch($insert_fields, $insert_values);
            }
        } catch (Exception $e) {
            if ($error) {
                $Task->cancel();
                throw new Exception($e->getMessage());
            }
        }
        return $this;
    }
    public function createImagePackage(
        $taskTypeID,
        $taskName = '',
        $shutdown = false,
        $debug = false,
        $deploySnapins = false,
        $isGroupTask = false,
        $username = '',
        $passreset = '',
        $sessionjoin = false,
        $wol = false
    ) {
        try {
            if (!$this->isValid()) {
                throw new Exception(self::$foglang['HostNotValid']);
            }
            $Task = $this->get('task');
            $TaskType = new TaskType($taskTypeID);
            if (!$TaskType->isValid()) {
                throw new Exception(self::$foglang['TaskTypeNotValid']);
            }
            if ($Task->isValid()) {
                $iTaskType = $Task->getTaskType()->isImagingTask();
                if ($iTaskType) {
                    throw new Exception(self::$foglang['InTask']);
                } elseif ($Task->isSnapinTasking()) {
                    if ($TaskType->get('id') == '13') {
                        $Task
                            ->set(
                                'name',
                                'Multiple Snapin task -- Altered after single'
                            )
                            ->set(
                                'typeID',
                                12
                            )->save();
                    } elseif ($TaskType->get('id') == '12') {
                        $this->cancelJobsSnapinsForHost();
                    } else {
                        $Task->cancel();
                        $Task = new Task(0);
                        $this->set('task', $Task);
                    }
                } else {
                    $Task->cancel();
                    $Task = new Task(0);
                    $this->set('task', $Task);
                }
            }
            unset($iTaskType);
            $Image = $this->getImage();
            $imagingTypes = $TaskType->isImagingTask();
            if ($imagingTypes) {
                if (!$Image->isValid()) {
                    throw new Exception(self::$foglang['ImageNotValid']);
                }
                if (!$Image->get('isEnabled')) {
                    throw new Exception(_('Image is not enabled'));
                }
                $StorageGroup = $Image->getStorageGroup();
                if (!$StorageGroup->isValid()) {
                    throw new Exception(self::$foglang['ImageGroupNotValid']);
                }
                if ($TaskType->isCapture()) {
                    $StorageNode = $StorageGroup->getMasterStorageNode();
                } else {
                    $StorageNode = $this->getOptimalStorageNode(
                        $this->get('imageID')
                    );
                }
                if (!$StorageNode->isValid()) {
                    $StorageNode = $StorageGroup->getOptimalStorageNode(
                        $this->get('imageID')
                    );
                }
                if (!$StorageNode->isValid()) {
                    $msg = sprintf(
                        '%s %s',
                        _('Could not find any'),
                        _('nodes containing this image')
                    );
                    throw new Exception($msg);
                }
                $imageTaskImgID = $this->get('imageID');
                $hostsWithImgID = self::getSubObjectIDs(
                    'Host',
                    array('imageID' => $imageTaskImgID)
                );
                $realImageID = self::getSubObjectIDs(
                    'Host',
                    array('id' => $this->get('id')),
                    'imageID'
                );
                if (!in_array($this->get('id'), $hostsWithImgID)) {
                    $this->set(
                        'imageID',
                        array_shift($realImageID)
                    )->save();
                }
                $this->set('imageID', $imageTaskImgID);
            }
            $isCapture = $TaskType->isCapture();
            $username = ($username ? $username : $_SESSION['FOG_USERNAME']);
            if (!$Task->isValid()) {
                $Task = $this->_createTasking(
                    $taskName,
                    $taskTypeID,
                    $username,
                    $imagingTypes ? $StorageGroup->get('id') : 0,
                    $imagingTypes ? $StorageNode->get('id') : 0,
                    $imagingTypes,
                    $shutdown,
                    $passreset,
                    $debug,
                    $wol
                );
                $Task->set('imageID', $this->get('imageID'));
                if (!$Task->save()) {
                    throw new Exception(self::$foglang['FailedTask']);
                }
                $this->set('task', $Task);
            }
            if ($TaskType->isSnapinTask()) {
                if ($deploySnapins === true) {
                    $deploySnapins = -1;
                }
                $mac = $this->get('mac');
                if ($deploySnapins) {
                    $this->createSnapinTasking($deploySnapins, $TaskType->isSnapinTasking(), $Task);
                }
            }
            if ($TaskType->isMulticast()) {
                $multicastTaskReturn = function (&$MulticastSessions) {
                    if (!$MulticastSessions->isValid()) {
                        return;
                    }
                    return $MulticastSessions;
                };
                $assoc = false;
                $showStates = array_merge(
                    $this->getQueuedStates(),
                    (array)$this->getProgressState()
                );
                if ($sessionjoin) {
                    $MCSessions = self::getClass('MulticastSessionsManager')
                        ->find(
                            array(
                                'name' => $taskName,
                                'stateID' => $showStates
                            )
                        );
                    $assoc = true;
                } else {
                    $MCSessions = self::getClass('MulticastSessionsManager')
                        ->find(
                            array(
                                'image' => $Image->get('id'),
                                'stateID' => $showStates
                            )
                        );
                }
                $MultiSessJoin = array_map(
                    $multicastTaskReturn,
                    $MCSessions
                );
                $MultiSessJoin = array_filter($MultiSessJoin);
                $MultiSessJoin = array_values($MultiSessJoin);
                if (is_array($MultiSessJoin) && count($MultiSessJoin)) {
                    $MulticastSession = array_shift($MultiSessJoin);
                }
                unset($MultiSessJoin);
                if ($MulticastSession instanceof MulticastSessions
                    && $MulticastSession->isValid()
                ) {
                    $assoc = true;
                } else {
                    $port = self::getSetting('FOG_UDPCAST_STARTINGPORT');
                    $portOverride = self::getSetting('FOG_MULTICAST_PORT_OVERRIDE');
                    $MulticastSession = self::getClass('MulticastSessions')
                        ->set('name', $taskName)
                        ->set('port', ($portOverride ? $portOverride : $port))
                        ->set('logpath', $this->getImage()->get('path'))
                        ->set('image', $this->getImage()->get('id'))
                        ->set('interface', $StorageNode->get('interface'))
                        ->set('stateID', 0)
                        ->set('starttime', self::niceDate()->format('Y-m-d H:i:s'))
                        ->set('percent', 0)
                        ->set('isDD', $this->getImage()->get('imageTypeID'))
                        ->set('NFSGroupID', $StorageNode->get('storageGroupID'))
                        ->set('clients', -1);
                    if ($MulticastSession->save()) {
                        $assoc = true;
                        if (!self::getSetting('FOG_MULTICAST_PORT_OVERRIDE')) {
                            $randomnumber = mt_rand(24576, 32766)*2;
                            while ($randomnumber == $MulticastSession->get('port')) {
                                $randomnumber = mt_rand(24576, 32766)*2;
                            }
                            $this->setSetting(
                                'FOG_UDPCAST_STARTINGPORT',
                                $randomnumber
                            );
                        }
                    }
                }
                if ($assoc) {
                    self::getClass('MulticastSessionsAssociation')
                        ->set('msID', $MulticastSession->get('id'))
                        ->set('taskID', $Task->get('id'))
                        ->save();
                }
            }
            if ($wol) {
                $this->wakeOnLAN();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        if ($taskTypeID == 14) {
            $Task->destroy();
        }
        return sprintf(
            '<li>%s &ndash; %s</li>',
            $this->get('name'),
            $this->getImage()->get('name')
        );
    }
    public function getImageMemberFromHostID()
    {
        try {
            $Image = $this->getImage();
            if (!$Image->isValid() || !$Image->get('id')) {
                throw new Exception(_('No Image defined for this host'));
            }
            if (!$Image->get('isEnabled')) {
                throw new Exception(_('Image is not enabled'));
            }
            $StorageGroup = $Image->getStorageGroup();
            if (!$StorageGroup->get('id')) {
                throw new Exception('No StorageGroup defined for this host');
            }
            $Task = self::getClass('Task')
                ->set('hostID', $this->get('id'))
                ->set('NFSGroupID', $StorageGroup->get('id'))
                ->set('NFSMemberID', $StorageGroup->getOptimalStorageNode($this->get('imageID'))->get('id'))
                ->set('imageID', $Image->get('id'));
        } catch (Exception $e) {
            self::$FOGCore->error(sprintf('%s():xError: %s', __FUNCTION__, $e->getMessage()));
            $Task = false;
        }
        return $Task;
    }
    public function clearAVRecordsForHost()
    {
        $MACs = $this->getMyMacs();
        self::getClass('VirusManager')->destroy(array('hostMAC'=>$MACs));
        unset($MACs);
    }
    public function wakeOnLAN()
    {
        $this->wakeUp($this->getMyMacs());
        return $this;
    }
    public function addAddMAC($addArray, $pending = false)
    {
        $addArray = array_map(function (&$item) {
            return trim(strtolower($item));
        }, (array)$addArray);
        $addTo = $pending ? 'pendingMACs' : 'additionalMACs';
        $pushItem = function (&$item) use (&$addTo) {
            $this->add($addTo, $item);
            unset($item);
        };
        array_map($pushItem, (array)$addArray);
        return $this;
    }
    public function addPendtoAdd($MACs = false)
    {
        $lowerAndTrim = function (&$MAC) {
            return trim(strtolower($MAC));
        };
        $PendMACs = array_map($lowerAndTrim, (array)$this->get('pendingMACs'));
        $MACs = array_map($lowerAndTrim, (array)$MACs);
        $matched = ($MACs === false ? array_intersect((array)$PendMACs, (array)$MACs) : $PendMACs);
        unset($MACs, $PendMACs);
        return $this->addAddMAC($matched)->removePendMAC($matched);
    }
    public function removeAddMAC($removeArray)
    {
        array_map(function (&$item) {
            $item = $item instanceof MACAddress ? $item : self::getClass('MACAddress', $item);
            $this->remove('additionalMACs', $item);
            unset($item);
        }, (array)$removeArray);
        return $this;
    }
    public function removePendMAC($removeArray)
    {
        array_map(function (&$item) {
            $item = $item instanceof MACAddress ? $item : self::getClass('MACAddress', $item);
            $this->remove('pendingMACs', $item);
            unset($item);
        }, (array)$removeArray);
        return $this;
    }
    public function addPriMAC($MAC)
    {
        return $this->set('mac', $MAC);
    }
    public function addPendMAC($MAC)
    {
        return $this->addAddMAC($MAC, true);
    }
    public function addPrinter($addArray)
    {
        return $this->addRemItem('printers', (array)$addArray, 'merge');
    }
    public function removePrinter($removeArray)
    {
        return $this->addRemItem('printers', (array)$removeArray, 'diff');
    }
    public function addSnapin($addArray)
    {
        $limit = self::getSetting('FOG_SNAPIN_LIMIT');
        if ($limit > 0) {
            if (self::getClass('SnapinManager')->count(array('id'=>$this->get('snapins'))) >= $limit || count($addArray) > $limit) {
                throw new Exception(sprintf('%s %d %s', _('You are only allowed to assign'), $limit, $limit == 1 ? _('snapin per host') : _('snapins per host')));
            }
        }
        return $this->addRemItem('snapins', (array)$addArray, 'merge');
    }
    public function removeSnapin($removeArray)
    {
        return $this->addRemItem('snapins', (array)$removeArray, 'diff');
    }
    public function addModule($addArray)
    {
        return $this->addRemItem('modules', (array)$addArray, 'merge');
    }
    public function removeModule($removeArray)
    {
        return $this->addRemItem('modules', (array)$removeArray, 'diff');
    }
    public function addPowerManagement($addArray)
    {
        return $this->addRemItem('powermanagementtasks', (array)$addArray, 'merge');
    }
    public function removePowerManagement($removeArray)
    {
        return $this->addRemItem('powermanagementtasks', (array)$removeArray, 'diff');
    }
    public function getMyMacs($justme = true)
    {
        if ($justme) {
            return self::getSubObjectIDs('MACAddressAssociation', array('hostID'=>$this->get('id')), 'mac');
        }
        return self::getSubObjectIDs('MACAddressAssociation', '', 'mac');
    }
    public function ignore($imageIgnore, $clientIgnore)
    {
        $MyMACs = $this->getMyMacs();
        $myMACs = $igMACs = $cgMACs = array();
        $macaddress = function (&$item) {
            $item = $item instanceof MACAddress ? $item : self::getClass('MACAddress', $item);
            if (!$item->isValid()) {
                return;
            }
            return trim(strtolower($item->__toString()));
        };
        $myMACs = array_values(array_filter(array_unique(array_map($macaddress, (array)$this->getMyMacs()))));
        $igMACs = array_values(array_filter(array_unique(array_map($macaddress, (array)$imageIgnore))));
        $cgMACs = array_values(array_filter(array_unique(array_map($macaddress, (array)$clientIgnore))));
        self::getClass('MACAddressAssociationManager')->update(array('mac'=>array_diff($myMACs, $cgMACs), 'hostID'=>$this->get('id')), '', array('clientIgnore'=>0));
        self::getClass('MACAddressAssociationManager')->update(array('mac'=>array_diff($myMACs, $igMACs), 'hostID'=>$this->get('id')), '', array('imageIgnore'=>0));
        if (count($cgMACs)) {
            self::getClass('MACAddressAssociationManager')->update(array('mac'=>$cgMACs, 'hostID'=>$this->get('id')), '', array('clientIgnore'=>1));
        }
        if (count($igMACs)) {
            self::getClass('MACAddressAssociationManager')->update(array('mac'=>$igMACs, 'hostID'=>$this->get('id')), '', array('imageIgnore'=>1));
        }
    }
    public function addGroup($addArray)
    {
        return $this->addHost($addArray);
    }
    public function removeGroup($removeArray)
    {
        return $this->removeHost($removeArray);
    }
    public function addHost($addArray)
    {
        return $this->addRemItem('groups', (array)$addArray, 'merge');
    }
    public function removeHost($removeArray)
    {
        return $this->addRemItem('groups', (array)$removeArray, 'diff');
    }
    public function clientMacCheck($MAC = false)
    {
        return $this->get('mac')->isClientIgnored() ? 'checked' : '';
    }
    public function imageMacCheck($MAC = false)
    {
        return $this->get('mac')->isImageIgnored() ? 'checked' : '';
    }
    public function setAD($useAD = '', $domain = '', $ou = '', $user = '', $pass = '', $override = false, $nosave = false, $legacy = '', $productKey = '', $enforce = '')
    {
        if ($this->get('id')) {
            if (!$override) {
                if (empty($useAD)) {
                    $useAD = $this->get('useAD');
                }
                if (empty($domain)) {
                    $domain = trim($this->get('ADDomain'));
                }
                if (empty($ou)) {
                    $ou = trim($this->get('ADOU'));
                }
                if (empty($user)) {
                    $user = trim($this->get('ADUser'));
                }
                if (empty($pass)) {
                    $pass = trim($this->encryptpw($this->get('ADPass')));
                }
                if (empty($legacy)) {
                    $legacy = trim($this->get('ADPassLegacy'));
                }
                if (empty($productKey)) {
                    $productKey = trim($this->encryptpw($this->get('productKey')));
                }
                if (empty($enforce)) {
                    $enforce = (int)$this->get('enforce');
                }
            }
        }
        if ($pass) {
            $pass = trim($this->encryptpw($pass));
        }
        $this->set('useAD', $useAD)
            ->set('ADDomain', trim($domain))
            ->set('ADOU', trim($ou))
            ->set('ADUser', trim($user))
            ->set('ADPass', trim($this->encryptpw($pass)))
            ->set('ADPassLegacy', $legacy)
            ->set('productKey', trim($this->encryptpw($productKey)))
            ->set('enforce', (int)$enforce);
        return $this;
    }
    public function getImage()
    {
        return self::getClass('Image', $this->get('imageID'));
    }
    public function getImageName()
    {
        return $this->get('imagename')->isValid() ? $this->get('imagename')->get('name') : '';
    }
    public function getOS()
    {
        return $this->getImage()->getOS()->get('name');
    }
    public function getActiveSnapinJob()
    {
        return $this->get('snapinjob');
    }
    public function setPingStatus()
    {
        $org_ip = $this->get('ip');
        if (filter_var($this->get('ip'), FILTER_VALIDATE_IP) === false) {
            $this->set('ip', self::$FOGCore->resolveHostname($this->get('name')));
        }
        if (filter_var($this->get('ip'), FILTER_VALIDATE_IP) === false) {
            $this->set('ip', $this->get('name'));
        }
        $this->getManager()->update(array('id'=>$this->get('id')), '', array('pingstatus'=>self::getClass('Ping', $this->get('ip'))->execute(), 'ip'=>$org_ip));
        unset($org_ip);
        return $this;
    }
    public function getPingCodeStr()
    {
        $val =  intval($this->get('pingstatus'));
        $socketstr = socket_strerror($val);
        $strtoupdate = "<i class=\"icon-ping-%s fa fa-exclamation-circle fa-1x\" style=\"color: %s\" title=\"$socketstr\"></i>";
        ob_start();
        if ($val === 0) {
            printf($strtoupdate, 'up', '#18f008');
        } else {
            printf($strtoupdate, 'down', '#ce0f0f');
        }
        return ob_get_clean();
    }
}
