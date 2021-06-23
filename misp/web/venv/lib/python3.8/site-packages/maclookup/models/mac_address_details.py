class MacAddressDetails:
    def __init__(self, values=None):
        self.search_term = None
        self.is_valid = None
        self.transmission_type = None
        self.administration_type = None

        if values is None:
            return

        if 'searchTerm' in values.keys():
            self.search_term = values['searchTerm']

        if 'isValid' in values.keys():
            self.is_valid = values['isValid']

        if 'transmissionType' in values.keys():
            self.transmission_type = values['transmissionType']

        if 'administrationType' in values.keys():
            self.administration_type = values['administrationType']

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
