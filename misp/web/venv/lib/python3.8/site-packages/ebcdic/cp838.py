""" 
Python Character Mapping Codec cp838 generated from 'temp/cp838.txt' with gencodec.py.
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
        name='cp838',
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
    '\u0e01'    # 0x42 -> THAI CHARACTER KO KAI
    '\u0e02'    # 0x43 -> THAI CHARACTER KHO KHAI
    '\u0e03'    # 0x44 -> THAI CHARACTER KHO KHUAT
    '\u0e04'    # 0x45 -> THAI CHARACTER KHO KHWAI
    '\u0e05'    # 0x46 -> THAI CHARACTER KHO KHON
    '\u0e06'    # 0x47 -> THAI CHARACTER KHO RAKHANG
    '\u0e07'    # 0x48 -> THAI CHARACTER NGO NGU
    '['         # 0x49 -> LEFT SQUARE BRACKET
    '\xa2'      # 0x4A -> CENT SIGN
    '.'         # 0x4B -> FULL STOP
    '<'         # 0x4C -> LESS-THAN SIGN
    '('         # 0x4D -> LEFT PARENTHESIS
    '+'         # 0x4E -> PLUS SIGN
    '|'         # 0x4F -> VERTICAL LINE
    '&'         # 0x50 -> AMPERSAND
    '\u0e48'    # 0x51 -> THAI CHARACTER MAI EK
    '\u0e08'    # 0x52 -> THAI CHARACTER CHO CHAN
    '\u0e09'    # 0x53 -> THAI CHARACTER CHO CHING
    '\u0e0a'    # 0x54 -> THAI CHARACTER CHO CHANG
    '\u0e0b'    # 0x55 -> THAI CHARACTER SO SO
    '\u0e0c'    # 0x56 -> THAI CHARACTER CHO CHOE
    '\u0e0d'    # 0x57 -> THAI CHARACTER YO YING
    '\u0e0e'    # 0x58 -> THAI CHARACTER DO CHADA
    ']'         # 0x59 -> RIGHT SQUARE BRACKET
    '!'         # 0x5A -> EXCLAMATION MARK
    '$'         # 0x5B -> DOLLAR SIGN
    '*'         # 0x5C -> ASTERISK
    ')'         # 0x5D -> RIGHT PARENTHESIS
    ';'         # 0x5E -> SEMICOLON
    '\xac'      # 0x5F -> NOT SIGN
    '-'         # 0x60 -> HYPHEN-MINUS
    '/'         # 0x61 -> SOLIDUS
    '\u0e0f'    # 0x62 -> THAI CHARACTER TO PATAK
    '\u0e10'    # 0x63 -> THAI CHARACTER THO THAN
    '\u0e11'    # 0x64 -> THAI CHARACTER THO NANGMONTHO
    '\u0e12'    # 0x65 -> THAI CHARACTER THO PHUTHAO
    '\u0e13'    # 0x66 -> THAI CHARACTER NO NEN
    '\u0e14'    # 0x67 -> THAI CHARACTER DO DEK
    '\u0e15'    # 0x68 -> THAI CHARACTER TO TAO
    '^'         # 0x69 -> CIRCUMFLEX ACCENT
    '\xa6'      # 0x6A -> BROKEN BAR
    ','         # 0x6B -> COMMA
    '%'         # 0x6C -> PERCENT SIGN
    '_'         # 0x6D -> LOW LINE
    '>'         # 0x6E -> GREATER-THAN SIGN
    '?'         # 0x6F -> QUESTION MARK
    '\u0e3f'    # 0x70 -> THAI CURRENCY SYMBOL BAHT
    '\u0e4e'    # 0x71 -> THAI CHARACTER YAMAKKAN
    '\u0e16'    # 0x72 -> THAI CHARACTER THO THUNG
    '\u0e17'    # 0x73 -> THAI CHARACTER THO THAHAN
    '\u0e18'    # 0x74 -> THAI CHARACTER THO THONG
    '\u0e19'    # 0x75 -> THAI CHARACTER NO NU
    '\u0e1a'    # 0x76 -> THAI CHARACTER BO BAIMAI
    '\u0e1b'    # 0x77 -> THAI CHARACTER PO PLA
    '\u0e1c'    # 0x78 -> THAI CHARACTER PHO PHUNG
    '`'         # 0x79 -> GRAVE ACCENT
    ':'         # 0x7A -> COLON
    '#'         # 0x7B -> NUMBER SIGN
    '@'         # 0x7C -> COMMERCIAL AT
    "'"         # 0x7D -> APOSTROPHE
    '='         # 0x7E -> EQUALS SIGN
    '"'         # 0x7F -> QUOTATION MARK
    '\u0e4f'    # 0x80 -> THAI CHARACTER FONGMAN
    'a'         # 0x81 -> LATIN SMALL LETTER A
    'b'         # 0x82 -> LATIN SMALL LETTER B
    'c'         # 0x83 -> LATIN SMALL LETTER C
    'd'         # 0x84 -> LATIN SMALL LETTER D
    'e'         # 0x85 -> LATIN SMALL LETTER E
    'f'         # 0x86 -> LATIN SMALL LETTER F
    'g'         # 0x87 -> LATIN SMALL LETTER G
    'h'         # 0x88 -> LATIN SMALL LETTER H
    'i'         # 0x89 -> LATIN SMALL LETTER I
    '\u0e1d'    # 0x8A -> THAI CHARACTER FO FA
    '\u0e1e'    # 0x8B -> THAI CHARACTER PHO PHAN
    '\u0e1f'    # 0x8C -> THAI CHARACTER FO FAN
    '\u0e20'    # 0x8D -> THAI CHARACTER PHO SAMPHAO
    '\u0e21'    # 0x8E -> THAI CHARACTER MO MA
    '\u0e22'    # 0x8F -> THAI CHARACTER YO YAK
    '\u0e5a'    # 0x90 -> THAI CHARACTER ANGKHANKHU
    'j'         # 0x91 -> LATIN SMALL LETTER J
    'k'         # 0x92 -> LATIN SMALL LETTER K
    'l'         # 0x93 -> LATIN SMALL LETTER L
    'm'         # 0x94 -> LATIN SMALL LETTER M
    'n'         # 0x95 -> LATIN SMALL LETTER N
    'o'         # 0x96 -> LATIN SMALL LETTER O
    'p'         # 0x97 -> LATIN SMALL LETTER P
    'q'         # 0x98 -> LATIN SMALL LETTER Q
    'r'         # 0x99 -> LATIN SMALL LETTER R
    '\u0e23'    # 0x9A -> THAI CHARACTER RO RUA
    '\u0e24'    # 0x9B -> THAI CHARACTER RU
    '\u0e25'    # 0x9C -> THAI CHARACTER LO LING
    '\u0e26'    # 0x9D -> THAI CHARACTER LU
    '\u0e27'    # 0x9E -> THAI CHARACTER WO WAEN
    '\u0e28'    # 0x9F -> THAI CHARACTER SO SALA
    '\u0e5b'    # 0xA0 -> THAI CHARACTER KHOMUT
    '~'         # 0xA1 -> TILDE
    's'         # 0xA2 -> LATIN SMALL LETTER S
    't'         # 0xA3 -> LATIN SMALL LETTER T
    'u'         # 0xA4 -> LATIN SMALL LETTER U
    'v'         # 0xA5 -> LATIN SMALL LETTER V
    'w'         # 0xA6 -> LATIN SMALL LETTER W
    'x'         # 0xA7 -> LATIN SMALL LETTER X
    'y'         # 0xA8 -> LATIN SMALL LETTER Y
    'z'         # 0xA9 -> LATIN SMALL LETTER Z
    '\u0e29'    # 0xAA -> THAI CHARACTER SO RUSI
    '\u0e2a'    # 0xAB -> THAI CHARACTER SO SUA
    '\u0e2b'    # 0xAC -> THAI CHARACTER HO HIP
    '\u0e2c'    # 0xAD -> THAI CHARACTER LO CHULA
    '\u0e2d'    # 0xAE -> THAI CHARACTER O ANG
    '\u0e2e'    # 0xAF -> THAI CHARACTER HO NOKHUK
    '\u0e50'    # 0xB0 -> THAI DIGIT ZERO
    '\u0e51'    # 0xB1 -> THAI DIGIT ONE
    '\u0e52'    # 0xB2 -> THAI DIGIT TWO
    '\u0e53'    # 0xB3 -> THAI DIGIT THREE
    '\u0e54'    # 0xB4 -> THAI DIGIT FOUR
    '\u0e55'    # 0xB5 -> THAI DIGIT FIVE
    '\u0e56'    # 0xB6 -> THAI DIGIT SIX
    '\u0e57'    # 0xB7 -> THAI DIGIT SEVEN
    '\u0e58'    # 0xB8 -> THAI DIGIT EIGHT
    '\u0e59'    # 0xB9 -> THAI DIGIT NINE
    '\u0e2f'    # 0xBA -> THAI CHARACTER PAIYANNOI
    '\u0e30'    # 0xBB -> THAI CHARACTER SARA A
    '\u0e31'    # 0xBC -> THAI CHARACTER MAI HAN-AKAT
    '\u0e32'    # 0xBD -> THAI CHARACTER SARA AA
    '\u0e33'    # 0xBE -> THAI CHARACTER SARA AM
    '\u0e34'    # 0xBF -> THAI CHARACTER SARA I
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
    '\u0e49'    # 0xCA -> THAI CHARACTER MAI THO
    '\u0e35'    # 0xCB -> THAI CHARACTER SARA II
    '\u0e36'    # 0xCC -> THAI CHARACTER SARA UE
    '\u0e37'    # 0xCD -> THAI CHARACTER SARA UEE
    '\u0e38'    # 0xCE -> THAI CHARACTER SARA U
    '\u0e39'    # 0xCF -> THAI CHARACTER SARA UU
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
    '\u0e3a'    # 0xDA -> THAI CHARACTER PHINTHU
    '\u0e40'    # 0xDB -> THAI CHARACTER SARA E
    '\u0e41'    # 0xDC -> THAI CHARACTER SARA AE
    '\u0e42'    # 0xDD -> THAI CHARACTER SARA O
    '\u0e43'    # 0xDE -> THAI CHARACTER SARA AI MAIMUAN
    '\u0e44'    # 0xDF -> THAI CHARACTER SARA AI MAIMALAI
    '\\'        # 0xE0 -> REVERSE SOLIDUS
    '\u0e4a'    # 0xE1 -> THAI CHARACTER MAI TRI
    'S'         # 0xE2 -> LATIN CAPITAL LETTER S
    'T'         # 0xE3 -> LATIN CAPITAL LETTER T
    'U'         # 0xE4 -> LATIN CAPITAL LETTER U
    'V'         # 0xE5 -> LATIN CAPITAL LETTER V
    'W'         # 0xE6 -> LATIN CAPITAL LETTER W
    'X'         # 0xE7 -> LATIN CAPITAL LETTER X
    'Y'         # 0xE8 -> LATIN CAPITAL LETTER Y
    'Z'         # 0xE9 -> LATIN CAPITAL LETTER Z
    '\u0e45'    # 0xEA -> THAI CHARACTER LAKKHANGYAO
    '\u0e46'    # 0xEB -> THAI CHARACTER MAIYAMOK
    '\u0e47'    # 0xEC -> THAI CHARACTER MAITAIKHU
    '\u0e48'    # 0xED -> THAI CHARACTER MAI EK
    '\u0e49'    # 0xEE -> THAI CHARACTER MAI THO
    '\u0e4a'    # 0xEF -> THAI CHARACTER MAI TRI
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
    '\u0e4b'    # 0xFA -> THAI CHARACTER MAI CHATTAWA
    '\u0e4c'    # 0xFB -> THAI CHARACTER THANTHAKHAT
    '\u0e4d'    # 0xFC -> THAI CHARACTER NIKHAHIT
    '\u0e4b'    # 0xFD -> THAI CHARACTER MAI CHATTAWA
    '\u0e4c'    # 0xFE -> THAI CHARACTER THANTHAKHAT
    '\x9f'      # 0xFF -> APPLICATION PROGRAM COMMAND
)


# Encoding table
encoding_table = codecs.charmap_build(decoding_table)
