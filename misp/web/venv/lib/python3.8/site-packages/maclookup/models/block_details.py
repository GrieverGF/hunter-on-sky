from dateutil.parser import parse


class BlockDetails:
    def __init__(self, values=None):
        self.block_found = None
        self.border_left = None
        self.border_right = None
        self.block_size = None
        self.assignment_block_size = None
        self.date_created = None
        self.date_updated = None

        if values is None:
            return

        if 'blockFound' in values.keys():
            self.block_found = values['blockFound']

        if 'borderLeft' in values.keys():
            self.border_left = values['borderLeft']

        if 'borderRight' in values.keys():
            self.border_right = values['borderRight']

        if 'blockSize' in values.keys():
            self.block_size = values['blockSize']

        if 'assignmentBlockSize' in values.keys():
            self.assignment_block_size = values['assignmentBlockSize']

        if 'dateCreated' in values.keys() and len(values['dateCreated']) > 1:
            self.date_created = parse(values['dateCreated'])

        if 'dateUpdated' in values.keys() and len(values['dateUpdated']) > 1:
            self.date_updated = parse(values['dateUpdated'])

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
