""" 
Python Character Mapping Codec cp833 generated from 'temp/cp833.txt' with gencodec.py.
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
        name='cp833',
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
    '\x85'      # 0x15 -> NEXT LINE (NEL)
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
    '\ufffd'    # 0x41 -> REPLACEMENT CHARACTER
    '\uffa0'    # 0x42 -> HALFWIDTH HANGUL FILLER
    '\uffa1'    # 0x43 -> HALFWIDTH HANGUL LETTER KIYEOK
    '\uffa2'    # 0x44 -> HALFWIDTH HANGUL LETTER SSANGKIYEOK
    '\uffa3'    # 0x45 -> HALFWIDTH HANGUL LETTER KIYEOK-SIOS
    '\uffa4'    # 0x46 -> HALFWIDTH HANGUL LETTER NIEUN
    '\uffa5'    # 0x47 -> HALFWIDTH HANGUL LETTER NIEUN-CIEUC
    '\uffa6'    # 0x48 -> HALFWIDTH HANGUL LETTER NIEUN-HIEUH
    '\uffa7'    # 0x49 -> HALFWIDTH HANGUL LETTER TIKEUT
    '\xa2'      # 0x4A -> CENT SIGN
    '.'         # 0x4B -> FULL STOP
    '<'         # 0x4C -> LESS-THAN SIGN
    '('         # 0x4D -> LEFT PARENTHESIS
    '+'         # 0x4E -> PLUS SIGN
    '|'         # 0x4F -> VERTICAL LINE
    '&'         # 0x50 -> AMPERSAND
    '\ufffd'    # 0x51 -> REPLACEMENT CHARACTER
    '\uffa8'    # 0x52 -> HALFWIDTH HANGUL LETTER SSANGTIKEUT
    '\uffa9'    # 0x53 -> HALFWIDTH HANGUL LETTER RIEUL
    '\uffaa'    # 0x54 -> HALFWIDTH HANGUL LETTER RIEUL-KIYEOK
    '\uffab'    # 0x55 -> HALFWIDTH HANGUL LETTER RIEUL-MIEUM
    '\uffac'    # 0x56 -> HALFWIDTH HANGUL LETTER RIEUL-PIEUP
    '\uffad'    # 0x57 -> HALFWIDTH HANGUL LETTER RIEUL-SIOS
    '\uffae'    # 0x58 -> HALFWIDTH HANGUL LETTER RIEUL-THIEUTH
    '\uffaf'    # 0x59 -> HALFWIDTH HANGUL LETTER RIEUL-PHIEUPH
    '!'         # 0x5A -> EXCLAMATION MARK
    '$'         # 0x5B -> DOLLAR SIGN
    '*'         # 0x5C -> ASTERISK
    ')'         # 0x5D -> RIGHT PARENTHESIS
    ';'         # 0x5E -> SEMICOLON
    '\xac'      # 0x5F -> NOT SIGN
    '-'         # 0x60 -> HYPHEN-MINUS
    '/'         # 0x61 -> SOLIDUS
    '\uffb0'    # 0x62 -> HALFWIDTH HANGUL LETTER RIEUL-HIEUH
    '\uffb1'    # 0x63 -> HALFWIDTH HANGUL LETTER MIEUM
    '\uffb2'    # 0x64 -> HALFWIDTH HANGUL LETTER PIEUP
    '\uffb3'    # 0x65 -> HALFWIDTH HANGUL LETTER SSANGPIEUP
    '\uffb4'    # 0x66 -> HALFWIDTH HANGUL LETTER PIEUP-SIOS
    '\uffb5'    # 0x67 -> HALFWIDTH HANGUL LETTER SIOS
    '\uffb6'    # 0x68 -> HALFWIDTH HANGUL LETTER SSANGSIOS
    '\uffb7'    # 0x69 -> HALFWIDTH HANGUL LETTER IEUNG
    '\xa6'      # 0x6A -> BROKEN BAR
    ','         # 0x6B -> COMMA
    '%'         # 0x6C -> PERCENT SIGN
    '_'         # 0x6D -> LOW LINE
    '>'         # 0x6E -> GREATER-THAN SIGN
    '?'         # 0x6F -> QUESTION MARK
    '['         # 0x70 -> LEFT SQUARE BRACKET
    '\ufffd'    # 0x71 -> REPLACEMENT CHARACTER
    '\uffb8'    # 0x72 -> HALFWIDTH HANGUL LETTER CIEUC
    '\uffb9'    # 0x73 -> HALFWIDTH HANGUL LETTER SSANGCIEUC
    '\uffba'    # 0x74 -> HALFWIDTH HANGUL LETTER CHIEUCH
    '\uffbb'    # 0x75 -> HALFWIDTH HANGUL LETTER KHIEUKH
    '\uffbc'    # 0x76 -> HALFWIDTH HANGUL LETTER THIEUTH
    '\uffbd'    # 0x77 -> HALFWIDTH HANGUL LETTER PHIEUPH
    '\uffbe'    # 0x78 -> HALFWIDTH HANGUL LETTER HIEUH
    '`'         # 0x79 -> GRAVE ACCENT
    ':'         # 0x7A -> COLON
    '#'         # 0x7B -> NUMBER SIGN
    '@'         # 0x7C -> COMMERCIAL AT
    "'"         # 0x7D -> APOSTROPHE
    '='         # 0x7E -> EQUALS SIGN
    '"'         # 0x7F -> QUOTATION MARK
    ']'         # 0x80 -> RIGHT SQUARE BRACKET
    'a'         # 0x81 -> LATIN SMALL LETTER A
    'b'         # 0x82 -> LATIN SMALL LETTER B
    'c'         # 0x83 -> LATIN SMALL LETTER C
    'd'         # 0x84 -> LATIN SMALL LETTER D
    'e'         # 0x85 -> LATIN SMALL LETTER E
    'f'         # 0x86 -> LATIN SMALL LETTER F
    'g'         # 0x87 -> LATIN SMALL LETTER G
    'h'         # 0x88 -> LATIN SMALL LETTER H
    'i'         # 0x89 -> LATIN SMALL LETTER I
    '\uffc2'    # 0x8A -> HALFWIDTH HANGUL LETTER A
    '\uffc3'    # 0x8B -> HALFWIDTH HANGUL LETTER AE
    '\uffc4'    # 0x8C -> HALFWIDTH HANGUL LETTER YA
    '\uffc5'    # 0x8D -> HALFWIDTH HANGUL LETTER YAE
    '\uffc6'    # 0x8E -> HALFWIDTH HANGUL LETTER EO
    '\uffc7'    # 0x8F -> HALFWIDTH HANGUL LETTER E
    '\ufffd'    # 0x90 -> REPLACEMENT CHARACTER
    'j'         # 0x91 -> LATIN SMALL LETTER J
    'k'         # 0x92 -> LATIN SMALL LETTER K
    'l'         # 0x93 -> LATIN SMALL LETTER L
    'm'         # 0x94 -> LATIN SMALL LETTER M
    'n'         # 0x95 -> LATIN SMALL LETTER N
    'o'         # 0x96 -> LATIN SMALL LETTER O
    'p'         # 0x97 -> LATIN SMALL LETTER P
    'q'         # 0x98 -> LATIN SMALL LETTER Q
    'r'         # 0x99 -> LATIN SMALL LETTER R
    '\uffca'    # 0x9A -> HALFWIDTH HANGUL LETTER YEO
    '\uffcb'    # 0x9B -> HALFWIDTH HANGUL LETTER YE
    '\uffcc'    # 0x9C -> HALFWIDTH HANGUL LETTER O
    '\uffcd'    # 0x9D -> HALFWIDTH HANGUL LETTER WA
    '\uffce'    # 0x9E -> HALFWIDTH HANGUL LETTER WAE
    '\uffcf'    # 0x9F -> HALFWIDTH HANGUL LETTER OE
    '\u203e'    # 0xA0 -> OVERLINE
    '~'         # 0xA1 -> TILDE
    's'         # 0xA2 -> LATIN SMALL LETTER S
    't'         # 0xA3 -> LATIN SMALL LETTER T
    'u'         # 0xA4 -> LATIN SMALL LETTER U
    'v'         # 0xA5 -> LATIN SMALL LETTER V
    'w'         # 0xA6 -> LATIN SMALL LETTER W
    'x'         # 0xA7 -> LATIN SMALL LETTER X
    'y'         # 0xA8 -> LATIN SMALL LETTER Y
    'z'         # 0xA9 -> LATIN SMALL LETTER Z
    '\uffd2'    # 0xAA -> HALFWIDTH HANGUL LETTER YO
    '\uffd3'    # 0xAB -> HALFWIDTH HANGUL LETTER U
    '\uffd4'    # 0xAC -> HALFWIDTH HANGUL LETTER WEO
    '\uffd5'    # 0xAD -> HALFWIDTH HANGUL LETTER WE
    '\uffd6'    # 0xAE -> HALFWIDTH HANGUL LETTER WI
    '\uffd7'    # 0xAF -> HALFWIDTH HANGUL LETTER YU
    '^'         # 0xB0 -> CIRCUMFLEX ACCENT
    '\ufffd'    # 0xB1 -> REPLACEMENT CHARACTER
    '\\'        # 0xB2 -> REVERSE SOLIDUS
    '\ufffd'    # 0xB3 -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xB4 -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xB5 -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xB6 -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xB7 -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xB8 -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xB9 -> REPLACEMENT CHARACTER
    '\uffda'    # 0xBA -> HALFWIDTH HANGUL LETTER EU
    '\uffdb'    # 0xBB -> HALFWIDTH HANGUL LETTER YI
    '\uffdc'    # 0xBC -> HALFWIDTH HANGUL LETTER I
    '\ufffd'    # 0xBD -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xBE -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xBF -> REPLACEMENT CHARACTER
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
    '\ufffd'    # 0xCA -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xCB -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xCC -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xCD -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xCE -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xCF -> REPLACEMENT CHARACTER
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
    '\ufffd'    # 0xDA -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xDB -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xDC -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xDD -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xDE -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xDF -> REPLACEMENT CHARACTER
    '\u20a9'    # 0xE0 -> WON SIGN
    '\ufffd'    # 0xE1 -> REPLACEMENT CHARACTER
    'S'         # 0xE2 -> LATIN CAPITAL LETTER S
    'T'         # 0xE3 -> LATIN CAPITAL LETTER T
    'U'         # 0xE4 -> LATIN CAPITAL LETTER U
    'V'         # 0xE5 -> LATIN CAPITAL LETTER V
    'W'         # 0xE6 -> LATIN CAPITAL LETTER W
    'X'         # 0xE7 -> LATIN CAPITAL LETTER X
    'Y'         # 0xE8 -> LATIN CAPITAL LETTER Y
    'Z'         # 0xE9 -> LATIN CAPITAL LETTER Z
    '\ufffd'    # 0xEA -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xEB -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xEC -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xED -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xEE -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xEF -> REPLACEMENT CHARACTER
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
    '\ufffd'    # 0xFA -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xFB -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xFC -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xFD -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xFE -> REPLACEMENT CHARACTER
    '\x9f'      # 0xFF -> APPLICATION PROGRAM COMMAND
)


# Encoding table
encoding_table = codecs.charmap_build(decoding_table)
