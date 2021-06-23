""" 
Python Character Mapping Codec cp1123 generated from 'temp/cp1123.txt' with gencodec.py.
"""
# Ensure the generated codec works with Python 2.6+.
from __future__ import unicode_literals

import codecs


# Codec APIs
class Codec(codecs.Codec):
    def encode(self, text, errors='strict'):
        return codecs.charmap_encode(text, errors, encoding_table)

    def decode(self, data, errors='strict'):
        return codecs.charmap_decode(data, errors, decoding_table)


class IncrementalEncoder(codecs.IncrementalEncoder):
    def encode(self, text, final=False):
        return codecs.charmap_encode(text, self.errors, encoding_table)[0]


class IncrementalDecoder(codecs.IncrementalDecoder):
    def decode(self, data, final=False):
        return codecs.charmap_decode(data, self.errors, decoding_table)[0]


class StreamWriter(Codec, codecs.StreamWriter):
    pass


class StreamReader(Codec, codecs.StreamReader):
    pass


# encodings module API
def getregentry():
    return codecs.CodecInfo(
        name='cp1123',
        encode=Codec().encode,
        decode=Codec().decode,
        incrementalencoder=IncrementalEncoder,
        incrementaldecoder=IncrementalDecoder,
        streamreader=StreamReader,
        streamwriter=StreamWriter,
    )


# Decoding Table

decoding_table = (
    '\x00'      # 0x00 -> NULL
    '\x01'      # 0x01 -> START OF HEADING
    '\x02'      # 0x02 -> START OF TEXT
    '\x03'      # 0x03 -> END OF TEXT
    '\x9c'      # 0x04 -> STRING TERMINATOR
    '\t'        # 0x05 -> CHARACTER TABULATION
    '\x86'      # 0x06 -> START OF SELECTED AREA
    '\x7f'      # 0x07 -> DELETE
    '\x97'      # 0x08 -> END OF GUARDED AREA
    '\x8d'      # 0x09 -> REVERSE LINE FEED
    '\x8e'      # 0x0A -> SINGLE SHIFT TWO
    '\x0b'      # 0x0B -> LINE TABULATION
    '\x0c'      # 0x0C -> FORM FEED (FF)
    '\r'        # 0x0D -> CARRIAGE RETURN (CR)
    '\x0e'      # 0x0E -> SHIFT OUT
    '\x0f'      # 0x0F -> SHIFT IN
    '\x10'      # 0x10 -> DATA LINK ESCAPE
    '\x11'      # 0x11 -> DEVICE CONTROL ONE
    '\x12'      # 0x12 -> DEVICE CONTROL TWO
    '\x13'      # 0x13 -> DEVICE CONTROL THREE
    '\x9d'      # 0x14 -> OPERATING SYSTEM COMMAND
    '\n'        # 0x15 -> LINE FEED (LF)
    '\x08'      # 0x16 -> BACKSPACE
    '\x87'      # 0x17 -> END OF SELECTED AREA
    '\x18'      # 0x18 -> CANCEL
    '\x19'      # 0x19 -> END OF MEDIUM
    '\x92'      # 0x1A -> PRIVATE USE TWO
    '\x8f'      # 0x1B -> SINGLE SHIFT THREE
    '\x1c'      # 0x1C -> INFORMATION SEPARATOR FOUR
    '\x1d'      # 0x1D -> INFORMATION SEPARATOR THREE
    '\x1e'      # 0x1E -> INFORMATION SEPARATOR TWO
    '\x1f'      # 0x1F -> INFORMATION SEPARATOR ONE
    '\x80'      # 0x20 -> PADDING CHARACTER
    '\x81'      # 0x21 -> HIGH OCTET PRESET
    '\x82'      # 0x22 -> BREAK PERMITTED HERE
    '\x83'      # 0x23 -> NO BREAK HERE
    '\x84'      # 0x24 -> LATIN 1 SUPPLEMENT 84
    '\n'        # 0x25 -> LINE FEED (LF)
    '\x17'      # 0x26 -> END OF TRANSMISSION BLOCK
    '\x1b'      # 0x27 -> ESCAPE
    '\x88'      # 0x28 -> CHARACTER TABULATION SET
    '\x89'      # 0x29 -> CHARACTER TABULATION WITH JUSTIFICATION
    '\x8a'      # 0x2A -> LINE TABULATION SET
    '\x8b'      # 0x2B -> PARTIAL LINE FORWARD
    '\x8c'      # 0x2C -> PARTIAL LINE BACKWARD
    '\x05'      # 0x2D -> ENQUIRY
    '\x06'      # 0x2E -> ACKNOWLEDGE
    '\x07'      # 0x2F -> BEL
    '\x90'      # 0x30 -> DEVICE CONTROL STRING
    '\x91'      # 0x31 -> PRIVATE USE ONE
    '\x16'      # 0x32 -> SYNCHRONOUS IDLE
    '\x93'      # 0x33 -> SET TRANSMIT STATE
    '\x94'      # 0x34 -> CANCEL CHARACTER
    '\x95'      # 0x35 -> MESSAGE WAITING
    '\x96'      # 0x36 -> START OF GUARDED AREA
    '\x04'      # 0x37 -> END OF TRANSMISSION
    '\x98'      # 0x38 -> START OF STRING
    '\x99'      # 0x39 -> SINGLE GRAPHIC CHARACTER INTRODUCER
    '\x9a'      # 0x3A -> SINGLE CHARACTER INTRODUCER
    '\x9b'      # 0x3B -> CONTROL SEQUENCE INTRODUCER
    '\x14'      # 0x3C -> DEVICE CONTROL FOUR
    '\x15'      # 0x3D -> NEGATIVE ACKNOWLEDGE
    '\x9e'      # 0x3E -> PRIVACY MESSAGE
    '\x1a'      # 0x3F -> SUBSTITUTE
    ' '         # 0x40 -> SPACE
    '\xa0'      # 0x41 -> NO-BREAK SPACE
    '\u0452'    # 0x42 -> CYRILLIC SMALL LETTER DJE
    '\u0491'    # 0x43 -> CYRILLIC SMALL LETTER GHE WITH UPTURN
    '\u0451'    # 0x44 -> CYRILLIC SMALL LETTER IO
    '\u0454'    # 0x45 -> CYRILLIC SMALL LETTER UKRAINIAN IE
    '\u0455'    # 0x46 -> CYRILLIC SMALL LETTER DZE
    '\u0456'    # 0x47 -> CYRILLIC SMALL LETTER BYELORUSSIAN-UKRAINIAN I
    '\u0457'    # 0x48 -> CYRILLIC SMALL LETTER YI
    '\u0458'    # 0x49 -> CYRILLIC SMALL LETTER JE
    '['         # 0x4A -> LEFT SQUARE BRACKET
    '.'         # 0x4B -> FULL STOP
    '<'         # 0x4C -> LESS-THAN SIGN
    '('         # 0x4D -> LEFT PARENTHESIS
    '+'         # 0x4E -> PLUS SIGN
    '!'         # 0x4F -> EXCLAMATION MARK
    '&'         # 0x50 -> AMPERSAND
    '\u0459'    # 0x51 -> CYRILLIC SMALL LETTER LJE
    '\u045a'    # 0x52 -> CYRILLIC SMALL LETTER NJE
    '\u045b'    # 0x53 -> CYRILLIC SMALL LETTER TSHE
    '\u045c'    # 0x54 -> CYRILLIC SMALL LETTER KJE
    '\u045e'    # 0x55 -> CYRILLIC SMALL LETTER SHORT U
    '\u045f'    # 0x56 -> CYRILLIC SMALL LETTER DZHE
    '\u042a'    # 0x57 -> CYRILLIC CAPITAL LETTER HARD SIGN
    '\u2116'    # 0x58 -> NUMERO SIGN
    '\u0402'    # 0x59 -> CYRILLIC CAPITAL LETTER DJE
    ']'         # 0x5A -> RIGHT SQUARE BRACKET
    '$'         # 0x5B -> DOLLAR SIGN
    '*'         # 0x5C -> ASTERISK
    ')'         # 0x5D -> RIGHT PARENTHESIS
    ';'         # 0x5E -> SEMICOLON
    '^'         # 0x5F -> CIRCUMFLEX ACCENT
    '-'         # 0x60 -> HYPHEN-MINUS
    '/'         # 0x61 -> SOLIDUS
    '\u0490'    # 0x62 -> CYRILLIC CAPITAL LETTER GHE WITH UPTURN
    '\u0401'    # 0x63 -> CYRILLIC CAPITAL LETTER IO
    '\u0404'    # 0x64 -> CYRILLIC CAPITAL LETTER UKRAINIAN IE
    '\u0405'    # 0x65 -> CYRILLIC CAPITAL LETTER DZE
    '\u0406'    # 0x66 -> CYRILLIC CAPITAL LETTER BYELORUSSIAN-UKRAINIAN I
    '\u0407'    # 0x67 -> CYRILLIC CAPITAL LETTER YI
    '\u0408'    # 0x68 -> CYRILLIC CAPITAL LETTER JE
    '\u0409'    # 0x69 -> CYRILLIC CAPITAL LETTER LJE
    '|'         # 0x6A -> VERTICAL LINE
    ','         # 0x6B -> COMMA
    '%'         # 0x6C -> PERCENT SIGN
    '_'         # 0x6D -> LOW LINE
    '>'         # 0x6E -> GREATER-THAN SIGN
    '?'         # 0x6F -> QUESTION MARK
    '\u040a'    # 0x70 -> CYRILLIC CAPITAL LETTER NJE
    '\u040b'    # 0x71 -> CYRILLIC CAPITAL LETTER TSHE
    '\u040c'    # 0x72 -> CYRILLIC CAPITAL LETTER KJE
    '\xad'      # 0x73 -> SOFT HYPHEN
    '\u040e'    # 0x74 -> CYRILLIC CAPITAL LETTER SHORT U
    '\u040f'    # 0x75 -> CYRILLIC CAPITAL LETTER DZHE
    '\u044e'    # 0x76 -> CYRILLIC SMALL LETTER YU
    '\u0430'    # 0x77 -> CYRILLIC SMALL LETTER A
    '\u0431'    # 0x78 -> CYRILLIC SMALL LETTER BE
    '`'         # 0x79 -> GRAVE ACCENT
    ':'         # 0x7A -> COLON
    '#'         # 0x7B -> NUMBER SIGN
    '@'         # 0x7C -> COMMERCIAL AT
    "'"         # 0x7D -> APOSTROPHE
    '='         # 0x7E -> EQUALS SIGN
    '"'         # 0x7F -> QUOTATION MARK
    '\u0446'    # 0x80 -> CYRILLIC SMALL LETTER TSE
    'a'         # 0x81 -> LATIN SMALL LETTER A
    'b'         # 0x82 -> LATIN SMALL LETTER B
    'c'         # 0x83 -> LATIN SMALL LETTER C
    'd'         # 0x84 -> LATIN SMALL LETTER D
    'e'         # 0x85 -> LATIN SMALL LETTER E
    'f'         # 0x86 -> LATIN SMALL LETTER F
    'g'         # 0x87 -> LATIN SMALL LETTER G
    'h'         # 0x88 -> LATIN SMALL LETTER H
    'i'         # 0x89 -> LATIN SMALL LETTER I
    '\u0434'    # 0x8A -> CYRILLIC SMALL LETTER DE
    '\u0435'    # 0x8B -> CYRILLIC SMALL LETTER IE
    '\u0444'    # 0x8C -> CYRILLIC SMALL LETTER EF
    '\u0433'    # 0x8D -> CYRILLIC SMALL LETTER GHE
    '\u0445'    # 0x8E -> CYRILLIC SMALL LETTER HA
    '\u0438'    # 0x8F -> CYRILLIC SMALL LETTER I
    '\u0439'    # 0x90 -> CYRILLIC SMALL LETTER SHORT I
    'j'         # 0x91 -> LATIN SMALL LETTER J
    'k'         # 0x92 -> LATIN SMALL LETTER K
    'l'         # 0x93 -> LATIN SMALL LETTER L
    'm'         # 0x94 -> LATIN SMALL LETTER M
    'n'         # 0x95 -> LATIN SMALL LETTER N
    'o'         # 0x96 -> LATIN SMALL LETTER O
    'p'         # 0x97 -> LATIN SMALL LETTER P
    'q'         # 0x98 -> LATIN SMALL LETTER Q
    'r'         # 0x99 -> LATIN SMALL LETTER R
    '\u043a'    # 0x9A -> CYRILLIC SMALL LETTER KA
    '\u043b'    # 0x9B -> CYRILLIC SMALL LETTER EL
    '\u043c'    # 0x9C -> CYRILLIC SMALL LETTER EM
    '\u043d'    # 0x9D -> CYRILLIC SMALL LETTER EN
    '\u043e'    # 0x9E -> CYRILLIC SMALL LETTER O
    '\u043f'    # 0x9F -> CYRILLIC SMALL LETTER PE
    '\u044f'    # 0xA0 -> CYRILLIC SMALL LETTER YA
    '~'         # 0xA1 -> TILDE
    's'         # 0xA2 -> LATIN SMALL LETTER S
    't'         # 0xA3 -> LATIN SMALL LETTER T
    'u'         # 0xA4 -> LATIN SMALL LETTER U
    'v'         # 0xA5 -> LATIN SMALL LETTER V
    'w'         # 0xA6 -> LATIN SMALL LETTER W
    'x'         # 0xA7 -> LATIN SMALL LETTER X
    'y'         # 0xA8 -> LATIN SMALL LETTER Y
    'z'         # 0xA9 -> LATIN SMALL LETTER Z
    '\u0440'    # 0xAA -> CYRILLIC SMALL LETTER ER
    '\u0441'    # 0xAB -> CYRILLIC SMALL LETTER ES
    '\u0442'    # 0xAC -> CYRILLIC SMALL LETTER TE
    '\u0443'    # 0xAD -> CYRILLIC SMALL LETTER U
    '\u0436'    # 0xAE -> CYRILLIC SMALL LETTER ZHE
    '\u0432'    # 0xAF -> CYRILLIC SMALL LETTER VE
    '\u044c'    # 0xB0 -> CYRILLIC SMALL LETTER SOFT SIGN
    '\u044b'    # 0xB1 -> CYRILLIC SMALL LETTER YERU
    '\u0437'    # 0xB2 -> CYRILLIC SMALL LETTER ZE
    '\u0448'    # 0xB3 -> CYRILLIC SMALL LETTER SHA
    '\u044d'    # 0xB4 -> CYRILLIC SMALL LETTER E
    '\u0449'    # 0xB5 -> CYRILLIC SMALL LETTER SHCHA
    '\u0447'    # 0xB6 -> CYRILLIC SMALL LETTER CHE
    '\u044a'    # 0xB7 -> CYRILLIC SMALL LETTER HARD SIGN
    '\u042e'    # 0xB8 -> CYRILLIC CAPITAL LETTER YU
    '\u0410'    # 0xB9 -> CYRILLIC CAPITAL LETTER A
    '\u0411'    # 0xBA -> CYRILLIC CAPITAL LETTER BE
    '\u0426'    # 0xBB -> CYRILLIC CAPITAL LETTER TSE
    '\u0414'    # 0xBC -> CYRILLIC CAPITAL LETTER DE
    '\u0415'    # 0xBD -> CYRILLIC CAPITAL LETTER IE
    '\u0424'    # 0xBE -> CYRILLIC CAPITAL LETTER EF
    '\u0413'    # 0xBF -> CYRILLIC CAPITAL LETTER GHE
    '{'         # 0xC0 -> LEFT CURLY BRACKET
    'A'         # 0xC1 -> LATIN CAPITAL LETTER A
    'B'         # 0xC2 -> LATIN CAPITAL LETTER B
    'C'         # 0xC3 -> LATIN CAPITAL LETTER C
    'D'         # 0xC4 -> LATIN CAPITAL LETTER D
    'E'         # 0xC5 -> LATIN CAPITAL LETTER E
    'F'         # 0xC6 -> LATIN CAPITAL LETTER F
    'G'         # 0xC7 -> LATIN CAPITAL LETTER G
    'H'         # 0xC8 -> LATIN CAPITAL LETTER H
    'I'         # 0xC9 -> LATIN CAPITAL LETTER I
    '\u0425'    # 0xCA -> CYRILLIC CAPITAL LETTER HA
    '\u0418'    # 0xCB -> CYRILLIC CAPITAL LETTER I
    '\u0419'    # 0xCC -> CYRILLIC CAPITAL LETTER SHORT I
    '\u041a'    # 0xCD -> CYRILLIC CAPITAL LETTER KA
    '\u041b'    # 0xCE -> CYRILLIC CAPITAL LETTER EL
    '\u041c'    # 0xCF -> CYRILLIC CAPITAL LETTER EM
    '}'         # 0xD0 -> RIGHT CURLY BRACKET
    'J'         # 0xD1 -> LATIN CAPITAL LETTER J
    'K'         # 0xD2 -> LATIN CAPITAL LETTER K
    'L'         # 0xD3 -> LATIN CAPITAL LETTER L
    'M'         # 0xD4 -> LATIN CAPITAL LETTER M
    'N'         # 0xD5 -> LATIN CAPITAL LETTER N
    'O'         # 0xD6 -> LATIN CAPITAL LETTER O
    'P'         # 0xD7 -> LATIN CAPITAL LETTER P
    'Q'         # 0xD8 -> LATIN CAPITAL LETTER Q
    'R'         # 0xD9 -> LATIN CAPITAL LETTER R
    '\u041d'    # 0xDA -> CYRILLIC CAPITAL LETTER EN
    '\u041e'    # 0xDB -> CYRILLIC CAPITAL LETTER O
    '\u041f'    # 0xDC -> CYRILLIC CAPITAL LETTER PE
    '\u042f'    # 0xDD -> CYRILLIC CAPITAL LETTER YA
    '\u0420'    # 0xDE -> CYRILLIC CAPITAL LETTER ER
    '\u0421'    # 0xDF -> CYRILLIC CAPITAL LETTER ES
    '\\'        # 0xE0 -> REVERSE SOLIDUS
    '\xa7'      # 0xE1 -> SECTION SIGN
    'S'         # 0xE2 -> LATIN CAPITAL LETTER S
    'T'         # 0xE3 -> LATIN CAPITAL LETTER T
    'U'         # 0xE4 -> LATIN CAPITAL LETTER U
    'V'         # 0xE5 -> LATIN CAPITAL LETTER V
    'W'         # 0xE6 -> LATIN CAPITAL LETTER W
    'X'         # 0xE7 -> LATIN CAPITAL LETTER X
    'Y'         # 0xE8 -> LATIN CAPITAL LETTER Y
    'Z'         # 0xE9 -> LATIN CAPITAL LETTER Z
    '\u0422'    # 0xEA -> CYRILLIC CAPITAL LETTER TE
    '\u0423'    # 0xEB -> CYRILLIC CAPITAL LETTER U
    '\u0416'    # 0xEC -> CYRILLIC CAPITAL LETTER ZHE
    '\u0412'    # 0xED -> CYRILLIC CAPITAL LETTER VE
    '\u042c'    # 0xEE -> CYRILLIC CAPITAL LETTER SOFT SIGN
    '\u042b'    # 0xEF -> CYRILLIC CAPITAL LETTER YERU
    '0'         # 0xF0 -> DIGIT ZERO
    '1'         # 0xF1 -> DIGIT ONE
    '2'         # 0xF2 -> DIGIT TWO
    '3'         # 0xF3 -> DIGIT THREE
    '4'         # 0xF4 -> DIGIT FOUR
    '5'         # 0xF5 -> DIGIT FIVE
    '6'         # 0xF6 -> DIGIT SIX
    '7'         # 0xF7 -> DIGIT SEVEN
    '8'         # 0xF8 -> DIGIT EIGHT
    '9'         # 0xF9 -> DIGIT NINE
    '\u0417'    # 0xFA -> CYRILLIC CAPITAL LETTER ZE
    '\u0428'    # 0xFB -> CYRILLIC CAPITAL LETTER SHA
    '\u042d'    # 0xFC -> CYRILLIC CAPITAL LETTER E
    '\u0429'    # 0xFD -> CYRILLIC CAPITAL LETTER SHCHA
    '\u0427'    # 0xFE -> CYRILLIC CAPITAL LETTER CHE
    '\x9f'      # 0xFF -> APPLICATION PROGRAM COMMAND
)


# Encoding table
encoding_table = codecs.charmap_build(decoding_table)
