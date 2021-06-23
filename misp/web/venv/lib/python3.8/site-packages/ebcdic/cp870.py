""" 
Python Character Mapping Codec cp870 generated from 'temp/cp870.txt' with gencodec.py.
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
        name='cp870',
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
    '\xe2'      # 0x42 -> LATIN SMALL LETTER A WITH CIRCUMFLEX
    '\xe4'      # 0x43 -> LATIN SMALL LETTER A WITH DIAERESIS
    '\u0163'    # 0x44 -> LATIN SMALL LETTER T WITH CEDILLA
    '\xe1'      # 0x45 -> LATIN SMALL LETTER A WITH ACUTE
    '\u0103'    # 0x46 -> LATIN SMALL LETTER A WITH BREVE
    '\u010d'    # 0x47 -> LATIN SMALL LETTER C WITH CARON
    '\xe7'      # 0x48 -> LATIN SMALL LETTER C WITH CEDILLA
    '\u0107'    # 0x49 -> LATIN SMALL LETTER C WITH ACUTE
    '['         # 0x4A -> LEFT SQUARE BRACKET
    '.'         # 0x4B -> FULL STOP
    '<'         # 0x4C -> LESS-THAN SIGN
    '('         # 0x4D -> LEFT PARENTHESIS
    '+'         # 0x4E -> PLUS SIGN
    '!'         # 0x4F -> EXCLAMATION MARK
    '&'         # 0x50 -> AMPERSAND
    '\xe9'      # 0x51 -> LATIN SMALL LETTER E WITH ACUTE
    '\u0119'    # 0x52 -> LATIN SMALL LETTER E WITH OGONEK
    '\xeb'      # 0x53 -> LATIN SMALL LETTER E WITH DIAERESIS
    '\u016f'    # 0x54 -> LATIN SMALL LETTER U WITH RING ABOVE
    '\xed'      # 0x55 -> LATIN SMALL LETTER I WITH ACUTE
    '\xee'      # 0x56 -> LATIN SMALL LETTER I WITH CIRCUMFLEX
    '\u013e'    # 0x57 -> LATIN SMALL LETTER L WITH CARON
    '\u013a'    # 0x58 -> LATIN SMALL LETTER L WITH ACUTE
    '\xdf'      # 0x59 -> LATIN SMALL LETTER SHARP S
    ']'         # 0x5A -> RIGHT SQUARE BRACKET
    '$'         # 0x5B -> DOLLAR SIGN
    '*'         # 0x5C -> ASTERISK
    ')'         # 0x5D -> RIGHT PARENTHESIS
    ';'         # 0x5E -> SEMICOLON
    '^'         # 0x5F -> CIRCUMFLEX ACCENT
    '-'         # 0x60 -> HYPHEN-MINUS
    '/'         # 0x61 -> SOLIDUS
    '\xc2'      # 0x62 -> LATIN CAPITAL LETTER A WITH CIRCUMFLEX
    '\xc4'      # 0x63 -> LATIN CAPITAL LETTER A WITH DIAERESIS
    '\u02dd'    # 0x64 -> DOUBLE ACUTE ACCENT
    '\xc1'      # 0x65 -> LATIN CAPITAL LETTER A WITH ACUTE
    '\u0102'    # 0x66 -> LATIN CAPITAL LETTER A WITH BREVE
    '\u010c'    # 0x67 -> LATIN CAPITAL LETTER C WITH CARON
    '\xc7'      # 0x68 -> LATIN CAPITAL LETTER C WITH CEDILLA
    '\u0106'    # 0x69 -> LATIN CAPITAL LETTER C WITH ACUTE
    '|'         # 0x6A -> VERTICAL LINE
    ','         # 0x6B -> COMMA
    '%'         # 0x6C -> PERCENT SIGN
    '_'         # 0x6D -> LOW LINE
    '>'         # 0x6E -> GREATER-THAN SIGN
    '?'         # 0x6F -> QUESTION MARK
    '\u02c7'    # 0x70 -> CARON
    '\xc9'      # 0x71 -> LATIN CAPITAL LETTER E WITH ACUTE
    '\u0118'    # 0x72 -> LATIN CAPITAL LETTER E WITH OGONEK
    '\xcb'      # 0x73 -> LATIN CAPITAL LETTER E WITH DIAERESIS
    '\u016e'    # 0x74 -> LATIN CAPITAL LETTER U WITH RING ABOVE
    '\xcd'      # 0x75 -> LATIN CAPITAL LETTER I WITH ACUTE
    '\xce'      # 0x76 -> LATIN CAPITAL LETTER I WITH CIRCUMFLEX
    '\u013d'    # 0x77 -> LATIN CAPITAL LETTER L WITH CARON
    '\u0139'    # 0x78 -> LATIN CAPITAL LETTER L WITH ACUTE
    '`'         # 0x79 -> GRAVE ACCENT
    ':'         # 0x7A -> COLON
    '#'         # 0x7B -> NUMBER SIGN
    '@'         # 0x7C -> COMMERCIAL AT
    "'"         # 0x7D -> APOSTROPHE
    '='         # 0x7E -> EQUALS SIGN
    '"'         # 0x7F -> QUOTATION MARK
    '\u02d8'    # 0x80 -> BREVE
    'a'         # 0x81 -> LATIN SMALL LETTER A
    'b'         # 0x82 -> LATIN SMALL LETTER B
    'c'         # 0x83 -> LATIN SMALL LETTER C
    'd'         # 0x84 -> LATIN SMALL LETTER D
    'e'         # 0x85 -> LATIN SMALL LETTER E
    'f'         # 0x86 -> LATIN SMALL LETTER F
    'g'         # 0x87 -> LATIN SMALL LETTER G
    'h'         # 0x88 -> LATIN SMALL LETTER H
    'i'         # 0x89 -> LATIN SMALL LETTER I
    '\u015b'    # 0x8A -> LATIN SMALL LETTER S WITH ACUTE
    '\u0148'    # 0x8B -> LATIN SMALL LETTER N WITH CARON
    '\u0111'    # 0x8C -> LATIN SMALL LETTER D WITH STROKE
    '\xfd'      # 0x8D -> LATIN SMALL LETTER Y WITH ACUTE
    '\u0159'    # 0x8E -> LATIN SMALL LETTER R WITH CARON
    '\u015f'    # 0x8F -> LATIN SMALL LETTER S WITH CEDILLA
    '\xb0'      # 0x90 -> DEGREE SIGN
    'j'         # 0x91 -> LATIN SMALL LETTER J
    'k'         # 0x92 -> LATIN SMALL LETTER K
    'l'         # 0x93 -> LATIN SMALL LETTER L
    'm'         # 0x94 -> LATIN SMALL LETTER M
    'n'         # 0x95 -> LATIN SMALL LETTER N
    'o'         # 0x96 -> LATIN SMALL LETTER O
    'p'         # 0x97 -> LATIN SMALL LETTER P
    'q'         # 0x98 -> LATIN SMALL LETTER Q
    'r'         # 0x99 -> LATIN SMALL LETTER R
    '\u0142'    # 0x9A -> LATIN SMALL LETTER L WITH STROKE
    '\u0144'    # 0x9B -> LATIN SMALL LETTER N WITH ACUTE
    '\u0161'    # 0x9C -> LATIN SMALL LETTER S WITH CARON
    '\xb8'      # 0x9D -> CEDILLA
    '\u02db'    # 0x9E -> OGONEK
    '\xa4'      # 0x9F -> CURRENCY SIGN
    '\u0105'    # 0xA0 -> LATIN SMALL LETTER A WITH OGONEK
    '~'         # 0xA1 -> TILDE
    's'         # 0xA2 -> LATIN SMALL LETTER S
    't'         # 0xA3 -> LATIN SMALL LETTER T
    'u'         # 0xA4 -> LATIN SMALL LETTER U
    'v'         # 0xA5 -> LATIN SMALL LETTER V
    'w'         # 0xA6 -> LATIN SMALL LETTER W
    'x'         # 0xA7 -> LATIN SMALL LETTER X
    'y'         # 0xA8 -> LATIN SMALL LETTER Y
    'z'         # 0xA9 -> LATIN SMALL LETTER Z
    '\u015a'    # 0xAA -> LATIN CAPITAL LETTER S WITH ACUTE
    '\u0147'    # 0xAB -> LATIN CAPITAL LETTER N WITH CARON
    '\u0110'    # 0xAC -> LATIN CAPITAL LETTER D WITH STROKE
    '\xdd'      # 0xAD -> LATIN CAPITAL LETTER Y WITH ACUTE
    '\u0158'    # 0xAE -> LATIN CAPITAL LETTER R WITH CARON
    '\u015e'    # 0xAF -> LATIN CAPITAL LETTER S WITH CEDILLA
    '\u02d9'    # 0xB0 -> DOT ABOVE
    '\u0104'    # 0xB1 -> LATIN CAPITAL LETTER A WITH OGONEK
    '\u017c'    # 0xB2 -> LATIN SMALL LETTER Z WITH DOT ABOVE
    '\u0162'    # 0xB3 -> LATIN CAPITAL LETTER T WITH CEDILLA
    '\u017b'    # 0xB4 -> LATIN CAPITAL LETTER Z WITH DOT ABOVE
    '\xa7'      # 0xB5 -> SECTION SIGN
    '\u017e'    # 0xB6 -> LATIN SMALL LETTER Z WITH CARON
    '\u017a'    # 0xB7 -> LATIN SMALL LETTER Z WITH ACUTE
    '\u017d'    # 0xB8 -> LATIN CAPITAL LETTER Z WITH CARON
    '\u0179'    # 0xB9 -> LATIN CAPITAL LETTER Z WITH ACUTE
    '\u0141'    # 0xBA -> LATIN CAPITAL LETTER L WITH STROKE
    '\u0143'    # 0xBB -> LATIN CAPITAL LETTER N WITH ACUTE
    '\u0160'    # 0xBC -> LATIN CAPITAL LETTER S WITH CARON
    '\xa8'      # 0xBD -> DIAERESIS
    '\xb4'      # 0xBE -> ACUTE ACCENT
    '\xd7'      # 0xBF -> MULTIPLICATION SIGN
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
    '\xad'      # 0xCA -> SOFT HYPHEN
    '\xf4'      # 0xCB -> LATIN SMALL LETTER O WITH CIRCUMFLEX
    '\xf6'      # 0xCC -> LATIN SMALL LETTER O WITH DIAERESIS
    '\u0155'    # 0xCD -> LATIN SMALL LETTER R WITH ACUTE
    '\xf3'      # 0xCE -> LATIN SMALL LETTER O WITH ACUTE
    '\u0151'    # 0xCF -> LATIN SMALL LETTER O WITH DOUBLE ACUTE
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
    '\u011a'    # 0xDA -> LATIN CAPITAL LETTER E WITH CARON
    '\u0171'    # 0xDB -> LATIN SMALL LETTER U WITH DOUBLE ACUTE
    '\xfc'      # 0xDC -> LATIN SMALL LETTER U WITH DIAERESIS
    '\u0165'    # 0xDD -> LATIN SMALL LETTER T WITH CARON
    '\xfa'      # 0xDE -> LATIN SMALL LETTER U WITH ACUTE
    '\u011b'    # 0xDF -> LATIN SMALL LETTER E WITH CARON
    '\\'        # 0xE0 -> REVERSE SOLIDUS
    '\xf7'      # 0xE1 -> DIVISION SIGN
    'S'         # 0xE2 -> LATIN CAPITAL LETTER S
    'T'         # 0xE3 -> LATIN CAPITAL LETTER T
    'U'         # 0xE4 -> LATIN CAPITAL LETTER U
    'V'         # 0xE5 -> LATIN CAPITAL LETTER V
    'W'         # 0xE6 -> LATIN CAPITAL LETTER W
    'X'         # 0xE7 -> LATIN CAPITAL LETTER X
    'Y'         # 0xE8 -> LATIN CAPITAL LETTER Y
    'Z'         # 0xE9 -> LATIN CAPITAL LETTER Z
    '\u010f'    # 0xEA -> LATIN SMALL LETTER D WITH CARON
    '\xd4'      # 0xEB -> LATIN CAPITAL LETTER O WITH CIRCUMFLEX
    '\xd6'      # 0xEC -> LATIN CAPITAL LETTER O WITH DIAERESIS
    '\u0154'    # 0xED -> LATIN CAPITAL LETTER R WITH ACUTE
    '\xd3'      # 0xEE -> LATIN CAPITAL LETTER O WITH ACUTE
    '\u0150'    # 0xEF -> LATIN CAPITAL LETTER O WITH DOUBLE ACUTE
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
    '\u010e'    # 0xFA -> LATIN CAPITAL LETTER D WITH CARON
    '\u0170'    # 0xFB -> LATIN CAPITAL LETTER U WITH DOUBLE ACUTE
    '\xdc'      # 0xFC -> LATIN CAPITAL LETTER U WITH DIAERESIS
    '\u0164'    # 0xFD -> LATIN CAPITAL LETTER T WITH CARON
    '\xda'      # 0xFE -> LATIN CAPITAL LETTER U WITH ACUTE
    '\x9f'      # 0xFF -> APPLICATION PROGRAM COMMAND
)


# Encoding table
encoding_table = codecs.charmap_build(decoding_table)
