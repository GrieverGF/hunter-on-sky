from .block_details import BlockDetails
from .vendor_details import VendorDetails
from .mac_address_details import MacAddressDetails


class ResponseModel:
    def __init__(self, values=None):
        self.vendor_details = None
        self.block_details = None
        self.mac_address_details = None

        if values is None:
            return

        if 'vendorDetails' in values.keys():
            self.vendor_details = VendorDetails(values['vendorDetails'])

        if 'blockDetails' in values.keys():
            self.block_details = BlockDetails(values['blockDetails'])

        if 'macAddressDetails' in values.keys():
            self.mac_address_details = MacAddressDetails(values['macAddressDetails'])

    def __str__(self):
        return str(self.__dict__)

    def __eq__(self, other):
        equal = True
        for i in self.__dict__.keys():
            equal &= self.__dict__[i] == other.__dict__[i]
        return equal
