""" 
Python Character Mapping Codec cp290 generated from 'temp/cp290.txt' with gencodec.py.
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
        name='cp290',
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
    '\uff61'    # 0x41 -> HALFWIDTH IDEOGRAPHIC FULL STOP
    '\uff62'    # 0x42 -> HALFWIDTH LEFT CORNER BRACKET
    '\uff63'    # 0x43 -> HALFWIDTH RIGHT CORNER BRACKET
    '\uff64'    # 0x44 -> HALFWIDTH IDEOGRAPHIC COMMA
    '\uff65'    # 0x45 -> HALFWIDTH KATAKANA MIDDLE DOT
    '\uff66'    # 0x46 -> HALFWIDTH KATAKANA LETTER WO
    '\uff67'    # 0x47 -> HALFWIDTH KATAKANA LETTER SMALL A
    '\uff68'    # 0x48 -> HALFWIDTH KATAKANA LETTER SMALL I
    '\uff69'    # 0x49 -> HALFWIDTH KATAKANA LETTER SMALL U
    '\xa3'      # 0x4A -> POUND SIGN
    '.'         # 0x4B -> FULL STOP
    '<'         # 0x4C -> LESS-THAN SIGN
    '('         # 0x4D -> LEFT PARENTHESIS
    '+'         # 0x4E -> PLUS SIGN
    '|'         # 0x4F -> VERTICAL LINE
    '&'         # 0x50 -> AMPERSAND
    '\uff6a'    # 0x51 -> HALFWIDTH KATAKANA LETTER SMALL E
    '\uff6b'    # 0x52 -> HALFWIDTH KATAKANA LETTER SMALL O
    '\uff6c'    # 0x53 -> HALFWIDTH KATAKANA LETTER SMALL YA
    '\uff6d'    # 0x54 -> HALFWIDTH KATAKANA LETTER SMALL YU
    '\uff6e'    # 0x55 -> HALFWIDTH KATAKANA LETTER SMALL YO
    '\uff6f'    # 0x56 -> HALFWIDTH KATAKANA LETTER SMALL TU
    '\ufffd'    # 0x57 -> REPLACEMENT CHARACTER
    '\uff70'    # 0x58 -> HALFWIDTH KATAKANA-HIRAGANA PROLONGED SOUND MARK
    '\ufffd'    # 0x59 -> REPLACEMENT CHARACTER
    '!'         # 0x5A -> EXCLAMATION MARK
    '\xa5'      # 0x5B -> YEN SIGN
    '*'         # 0x5C -> ASTERISK
    ')'         # 0x5D -> RIGHT PARENTHESIS
    ';'         # 0x5E -> SEMICOLON
    '\xac'      # 0x5F -> NOT SIGN
    '-'         # 0x60 -> HYPHEN-MINUS
    '/'         # 0x61 -> SOLIDUS
    'a'         # 0x62 -> LATIN SMALL LETTER A
    'b'         # 0x63 -> LATIN SMALL LETTER B
    'c'         # 0x64 -> LATIN SMALL LETTER C
    'd'         # 0x65 -> LATIN SMALL LETTER D
    'e'         # 0x66 -> LATIN SMALL LETTER E
    'f'         # 0x67 -> LATIN SMALL LETTER F
    'g'         # 0x68 -> LATIN SMALL LETTER G
    'h'         # 0x69 -> LATIN SMALL LETTER H
    '\ufffd'    # 0x6A -> REPLACEMENT CHARACTER
    ','         # 0x6B -> COMMA
    '%'         # 0x6C -> PERCENT SIGN
    '_'         # 0x6D -> LOW LINE
    '>'         # 0x6E -> GREATER-THAN SIGN
    '?'         # 0x6F -> QUESTION MARK
    '['         # 0x70 -> LEFT SQUARE BRACKET
    'i'         # 0x71 -> LATIN SMALL LETTER I
    'j'         # 0x72 -> LATIN SMALL LETTER J
    'k'         # 0x73 -> LATIN SMALL LETTER K
    'l'         # 0x74 -> LATIN SMALL LETTER L
    'm'         # 0x75 -> LATIN SMALL LETTER M
    'n'         # 0x76 -> LATIN SMALL LETTER N
    'o'         # 0x77 -> LATIN SMALL LETTER O
    'p'         # 0x78 -> LATIN SMALL LETTER P
    '`'         # 0x79 -> GRAVE ACCENT
    ':'         # 0x7A -> COLON
    '#'         # 0x7B -> NUMBER SIGN
    '@'         # 0x7C -> COMMERCIAL AT
    "'"         # 0x7D -> APOSTROPHE
    '='         # 0x7E -> EQUALS SIGN
    '"'         # 0x7F -> QUOTATION MARK
    ']'         # 0x80 -> RIGHT SQUARE BRACKET
    '\uff71'    # 0x81 -> HALFWIDTH KATAKANA LETTER A
    '\uff72'    # 0x82 -> HALFWIDTH KATAKANA LETTER I
    '\uff73'    # 0x83 -> HALFWIDTH KATAKANA LETTER U
    '\uff74'    # 0x84 -> HALFWIDTH KATAKANA LETTER E
    '\uff75'    # 0x85 -> HALFWIDTH KATAKANA LETTER O
    '\uff76'    # 0x86 -> HALFWIDTH KATAKANA LETTER KA
    '\uff77'    # 0x87 -> HALFWIDTH KATAKANA LETTER KI
    '\uff78'    # 0x88 -> HALFWIDTH KATAKANA LETTER KU
    '\uff79'    # 0x89 -> HALFWIDTH KATAKANA LETTER KE
    '\uff7a'    # 0x8A -> HALFWIDTH KATAKANA LETTER KO
    'q'         # 0x8B -> LATIN SMALL LETTER Q
    '\uff7b'    # 0x8C -> HALFWIDTH KATAKANA LETTER SA
    '\uff7c'    # 0x8D -> HALFWIDTH KATAKANA LETTER SI
    '\uff7d'    # 0x8E -> HALFWIDTH KATAKANA LETTER SU
    '\uff7e'    # 0x8F -> HALFWIDTH KATAKANA LETTER SE
    '\uff7f'    # 0x90 -> HALFWIDTH KATAKANA LETTER SO
    '\uff80'    # 0x91 -> HALFWIDTH KATAKANA LETTER TA
    '\uff81'    # 0x92 -> HALFWIDTH KATAKANA LETTER TI
    '\uff82'    # 0x93 -> HALFWIDTH KATAKANA LETTER TU
    '\uff83'    # 0x94 -> HALFWIDTH KATAKANA LETTER TE
    '\uff84'    # 0x95 -> HALFWIDTH KATAKANA LETTER TO
    '\uff85'    # 0x96 -> HALFWIDTH KATAKANA LETTER NA
    '\uff86'    # 0x97 -> HALFWIDTH KATAKANA LETTER NI
    '\uff87'    # 0x98 -> HALFWIDTH KATAKANA LETTER NU
    '\uff88'    # 0x99 -> HALFWIDTH KATAKANA LETTER NE
    '\uff89'    # 0x9A -> HALFWIDTH KATAKANA LETTER NO
    'r'         # 0x9B -> LATIN SMALL LETTER R
    '\ufffd'    # 0x9C -> REPLACEMENT CHARACTER
    '\uff8a'    # 0x9D -> HALFWIDTH KATAKANA LETTER HA
    '\uff8b'    # 0x9E -> HALFWIDTH KATAKANA LETTER HI
    '\uff8c'    # 0x9F -> HALFWIDTH KATAKANA LETTER HU
    '~'         # 0xA0 -> TILDE
    '\u203e'    # 0xA1 -> OVERLINE
    '\uff8d'    # 0xA2 -> HALFWIDTH KATAKANA LETTER HE
    '\uff8e'    # 0xA3 -> HALFWIDTH KATAKANA LETTER HO
    '\uff8f'    # 0xA4 -> HALFWIDTH KATAKANA LETTER MA
    '\uff90'    # 0xA5 -> HALFWIDTH KATAKANA LETTER MI
    '\uff91'    # 0xA6 -> HALFWIDTH KATAKANA LETTER MU
    '\uff92'    # 0xA7 -> HALFWIDTH KATAKANA LETTER ME
    '\uff93'    # 0xA8 -> HALFWIDTH KATAKANA LETTER MO
    '\uff94'    # 0xA9 -> HALFWIDTH KATAKANA LETTER YA
    '\uff95'    # 0xAA -> HALFWIDTH KATAKANA LETTER YU
    's'         # 0xAB -> LATIN SMALL LETTER S
    '\uff96'    # 0xAC -> HALFWIDTH KATAKANA LETTER YO
    '\uff97'    # 0xAD -> HALFWIDTH KATAKANA LETTER RA
    '\uff98'    # 0xAE -> HALFWIDTH KATAKANA LETTER RI
    '\uff99'    # 0xAF -> HALFWIDTH KATAKANA LETTER RU
    '^'         # 0xB0 -> CIRCUMFLEX ACCENT
    '\xa2'      # 0xB1 -> CENT SIGN
    '\\'        # 0xB2 -> REVERSE SOLIDUS
    't'         # 0xB3 -> LATIN SMALL LETTER T
    'u'         # 0xB4 -> LATIN SMALL LETTER U
    'v'         # 0xB5 -> LATIN SMALL LETTER V
    'w'         # 0xB6 -> LATIN SMALL LETTER W
    'x'         # 0xB7 -> LATIN SMALL LETTER X
    'y'         # 0xB8 -> LATIN SMALL LETTER Y
    'z'         # 0xB9 -> LATIN SMALL LETTER Z
    '\uff9a'    # 0xBA -> HALFWIDTH KATAKANA LETTER RE
    '\uff9b'    # 0xBB -> HALFWIDTH KATAKANA LETTER RO
    '\uff9c'    # 0xBC -> HALFWIDTH KATAKANA LETTER WA
    '\uff9d'    # 0xBD -> HALFWIDTH KATAKANA LETTER N
    '\uff9e'    # 0xBE -> HALFWIDTH KATAKANA VOICED SOUND MARK
    '\uff9f'    # 0xBF -> HALFWIDTH KATAKANA SEMI-VOICED SOUND MARK
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
    '$'         # 0xE0 -> DOLLAR SIGN
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
