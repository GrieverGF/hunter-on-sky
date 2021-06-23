class VendorDetails:
    def __init__(self, values=None):
        self.oui = None
        self.is_private = None
        self.company_name = None
        self.company_address = None
        self.country_code = None

        if values is None:
            return

        if 'oui' in values.keys():
            self.oui = values['oui']

        if 'isPrivate' in values.keys():
            self.is_private= values['isPrivate']

        if 'companyName' in values.keys():
            self.company_name = values['companyName']

        if 'companyAddress' in values.keys():
            self.company_address = values['companyAddress']

        if 'countryCode' in values.keys():
            self.country_code = values['countryCode']

    def __str__(self):
        return str(self.__dict__)

    def __eq__(self, other):
        equal = True
        for i in self.__dict__.keys():
            if i in other.__dict__.keys():
                equal &= self.__dict__[i] == other.__dict__[i]
            else:
                equal = False
        return equal
