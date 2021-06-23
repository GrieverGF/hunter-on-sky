""" 
Python Character Mapping Codec cp1097 generated from 'temp/cp1097.txt' with gencodec.py.
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
        name='cp1097',
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
    '\xa0'      # 0x41 -> NO-BREAK SPACE
    '\u060c'    # 0x42 -> ARABIC COMMA
    '\u064b'    # 0x43 -> ARABIC FATHATAN
    '\ufe81'    # 0x44 -> ARABIC LETTER ALEF WITH MADDA ABOVE ISOLATED FORM
    '\ufe82'    # 0x45 -> ARABIC LETTER ALEF WITH MADDA ABOVE FINAL FORM
    '\uf8fa'    # 0x46 -> PRIVATE USE AREA F8FA
    '\ufe8d'    # 0x47 -> ARABIC LETTER ALEF ISOLATED FORM
    '\ufe8e'    # 0x48 -> ARABIC LETTER ALEF FINAL FORM
    '\uf8fb'    # 0x49 -> PRIVATE USE AREA F8FB
    '\xa4'      # 0x4A -> CURRENCY SIGN
    '.'         # 0x4B -> FULL STOP
    '<'         # 0x4C -> LESS-THAN SIGN
    '('         # 0x4D -> LEFT PARENTHESIS
    '+'         # 0x4E -> PLUS SIGN
    '|'         # 0x4F -> VERTICAL LINE
    '&'         # 0x50 -> AMPERSAND
    '\ufe80'    # 0x51 -> ARABIC LETTER HAMZA ISOLATED FORM
    '\ufe83'    # 0x52 -> ARABIC LETTER ALEF WITH HAMZA ABOVE ISOLATED FORM
    '\ufe84'    # 0x53 -> ARABIC LETTER ALEF WITH HAMZA ABOVE FINAL FORM
    '\uf8f9'    # 0x54 -> PRIVATE USE AREA F8F9
    '\ufe85'    # 0x55 -> ARABIC LETTER WAW WITH HAMZA ABOVE ISOLATED FORM
    '\ufe8b'    # 0x56 -> ARABIC LETTER YEH WITH HAMZA ABOVE INITIAL FORM
    '\ufe8f'    # 0x57 -> ARABIC LETTER BEH ISOLATED FORM
    '\ufe91'    # 0x58 -> ARABIC LETTER BEH INITIAL FORM
    '\ufb56'    # 0x59 -> ARABIC LETTER PEH ISOLATED FORM
    '!'         # 0x5A -> EXCLAMATION MARK
    '$'         # 0x5B -> DOLLAR SIGN
    '*'         # 0x5C -> ASTERISK
    ')'         # 0x5D -> RIGHT PARENTHESIS
    ';'         # 0x5E -> SEMICOLON
    '\xac'      # 0x5F -> NOT SIGN
    '-'         # 0x60 -> HYPHEN-MINUS
    '/'         # 0x61 -> SOLIDUS
    '\ufb58'    # 0x62 -> ARABIC LETTER PEH INITIAL FORM
    '\ufe95'    # 0x63 -> ARABIC LETTER TEH ISOLATED FORM
    '\ufe97'    # 0x64 -> ARABIC LETTER TEH INITIAL FORM
    '\ufe99'    # 0x65 -> ARABIC LETTER THEH ISOLATED FORM
    '\ufe9b'    # 0x66 -> ARABIC LETTER THEH INITIAL FORM
    '\ufe9d'    # 0x67 -> ARABIC LETTER JEEM ISOLATED FORM
    '\ufe9f'    # 0x68 -> ARABIC LETTER JEEM INITIAL FORM
    '\ufb7a'    # 0x69 -> ARABIC LETTER TCHEH ISOLATED FORM
    '\u061b'    # 0x6A -> ARABIC SEMICOLON
    ','         # 0x6B -> COMMA
    '%'         # 0x6C -> PERCENT SIGN
    '_'         # 0x6D -> LOW LINE
    '>'         # 0x6E -> GREATER-THAN SIGN
    '?'         # 0x6F -> QUESTION MARK
    '\ufb7c'    # 0x70 -> ARABIC LETTER TCHEH INITIAL FORM
    '\ufea1'    # 0x71 -> ARABIC LETTER HAH ISOLATED FORM
    '\ufea3'    # 0x72 -> ARABIC LETTER HAH INITIAL FORM
    '\ufea5'    # 0x73 -> ARABIC LETTER KHAH ISOLATED FORM
    '\ufea7'    # 0x74 -> ARABIC LETTER KHAH INITIAL FORM
    '\ufea9'    # 0x75 -> ARABIC LETTER DAL ISOLATED FORM
    '\ufeab'    # 0x76 -> ARABIC LETTER THAL ISOLATED FORM
    '\ufead'    # 0x77 -> ARABIC LETTER REH ISOLATED FORM
    '\ufeaf'    # 0x78 -> ARABIC LETTER ZAIN ISOLATED FORM
    '`'         # 0x79 -> GRAVE ACCENT
    ':'         # 0x7A -> COLON
    '#'         # 0x7B -> NUMBER SIGN
    '@'         # 0x7C -> COMMERCIAL AT
    "'"         # 0x7D -> APOSTROPHE
    '='         # 0x7E -> EQUALS SIGN
    '"'         # 0x7F -> QUOTATION MARK
    '\ufb8a'    # 0x80 -> ARABIC LETTER JEH ISOLATED FORM
    'a'         # 0x81 -> LATIN SMALL LETTER A
    'b'         # 0x82 -> LATIN SMALL LETTER B
    'c'         # 0x83 -> LATIN SMALL LETTER C
    'd'         # 0x84 -> LATIN SMALL LETTER D
    'e'         # 0x85 -> LATIN SMALL LETTER E
    'f'         # 0x86 -> LATIN SMALL LETTER F
    'g'         # 0x87 -> LATIN SMALL LETTER G
    'h'         # 0x88 -> LATIN SMALL LETTER H
    'i'         # 0x89 -> LATIN SMALL LETTER I
    '\xab'      # 0x8A -> LEFT-POINTING DOUBLE ANGLE QUOTATION MARK
    '\xbb'      # 0x8B -> RIGHT-POINTING DOUBLE ANGLE QUOTATION MARK
    '\ufeb1'    # 0x8C -> ARABIC LETTER SEEN ISOLATED FORM
    '\ufeb3'    # 0x8D -> ARABIC LETTER SEEN INITIAL FORM
    '\ufeb5'    # 0x8E -> ARABIC LETTER SHEEN ISOLATED FORM
    '\ufeb7'    # 0x8F -> ARABIC LETTER SHEEN INITIAL FORM
    '\ufeb9'    # 0x90 -> ARABIC LETTER SAD ISOLATED FORM
    'j'         # 0x91 -> LATIN SMALL LETTER J
    'k'         # 0x92 -> LATIN SMALL LETTER K
    'l'         # 0x93 -> LATIN SMALL LETTER L
    'm'         # 0x94 -> LATIN SMALL LETTER M
    'n'         # 0x95 -> LATIN SMALL LETTER N
    'o'         # 0x96 -> LATIN SMALL LETTER O
    'p'         # 0x97 -> LATIN SMALL LETTER P
    'q'         # 0x98 -> LATIN SMALL LETTER Q
    'r'         # 0x99 -> LATIN SMALL LETTER R
    '\ufebb'    # 0x9A -> ARABIC LETTER SAD INITIAL FORM
    '\ufebd'    # 0x9B -> ARABIC LETTER DAD ISOLATED FORM
    '\ufebf'    # 0x9C -> ARABIC LETTER DAD INITIAL FORM
    '\ufec1'    # 0x9D -> ARABIC LETTER TAH ISOLATED FORM
    '\ufec3'    # 0x9E -> ARABIC LETTER TAH INITIAL FORM
    '\ufec5'    # 0x9F -> ARABIC LETTER ZAH ISOLATED FORM
    '\ufec7'    # 0xA0 -> ARABIC LETTER ZAH INITIAL FORM
    '~'         # 0xA1 -> TILDE
    's'         # 0xA2 -> LATIN SMALL LETTER S
    't'         # 0xA3 -> LATIN SMALL LETTER T
    'u'         # 0xA4 -> LATIN SMALL LETTER U
    'v'         # 0xA5 -> LATIN SMALL LETTER V
    'w'         # 0xA6 -> LATIN SMALL LETTER W
    'x'         # 0xA7 -> LATIN SMALL LETTER X
    'y'         # 0xA8 -> LATIN SMALL LETTER Y
    'z'         # 0xA9 -> LATIN SMALL LETTER Z
    '\ufec9'    # 0xAA -> ARABIC LETTER AIN ISOLATED FORM
    '\ufeca'    # 0xAB -> ARABIC LETTER AIN FINAL FORM
    '\ufecb'    # 0xAC -> ARABIC LETTER AIN INITIAL FORM
    '\ufecc'    # 0xAD -> ARABIC LETTER AIN MEDIAL FORM
    '\ufecd'    # 0xAE -> ARABIC LETTER GHAIN ISOLATED FORM
    '\ufece'    # 0xAF -> ARABIC LETTER GHAIN FINAL FORM
    '\ufecf'    # 0xB0 -> ARABIC LETTER GHAIN INITIAL FORM
    '\ufed0'    # 0xB1 -> ARABIC LETTER GHAIN MEDIAL FORM
    '\ufed1'    # 0xB2 -> ARABIC LETTER FEH ISOLATED FORM
    '\ufed3'    # 0xB3 -> ARABIC LETTER FEH INITIAL FORM
    '\ufed5'    # 0xB4 -> ARABIC LETTER QAF ISOLATED FORM
    '\ufed7'    # 0xB5 -> ARABIC LETTER QAF INITIAL FORM
    '\ufb8e'    # 0xB6 -> ARABIC LETTER KEHEH ISOLATED FORM
    '\ufedb'    # 0xB7 -> ARABIC LETTER KAF INITIAL FORM
    '\ufb92'    # 0xB8 -> ARABIC LETTER GAF ISOLATED FORM
    '\ufb94'    # 0xB9 -> ARABIC LETTER GAF INITIAL FORM
    '['         # 0xBA -> LEFT SQUARE BRACKET
    ']'         # 0xBB -> RIGHT SQUARE BRACKET
    '\ufedd'    # 0xBC -> ARABIC LETTER LAM ISOLATED FORM
    '\ufedf'    # 0xBD -> ARABIC LETTER LAM INITIAL FORM
    '\ufee1'    # 0xBE -> ARABIC LETTER MEEM ISOLATED FORM
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
    '\ufee3'    # 0xCB -> ARABIC LETTER MEEM INITIAL FORM
    '\ufee5'    # 0xCC -> ARABIC LETTER NOON ISOLATED FORM
    '\ufee7'    # 0xCD -> ARABIC LETTER NOON INITIAL FORM
    '\ufeed'    # 0xCE -> ARABIC LETTER WAW ISOLATED FORM
    '\ufee9'    # 0xCF -> ARABIC LETTER HEH ISOLATED FORM
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
    '\ufeeb'    # 0xDA -> ARABIC LETTER HEH INITIAL FORM
    '\ufeec'    # 0xDB -> ARABIC LETTER HEH MEDIAL FORM
    '\ufba4'    # 0xDC -> ARABIC LETTER HEH WITH YEH ABOVE ISOLATED FORM
    '\ufbfc'    # 0xDD -> ARABIC LETTER FARSI YEH ISOLATED FORM
    '\ufbfd'    # 0xDE -> ARABIC LETTER FARSI YEH FINAL FORM
    '\ufbfe'    # 0xDF -> ARABIC LETTER FARSI YEH INITIAL FORM
    '\\'        # 0xE0 -> REVERSE SOLIDUS
    '\u061f'    # 0xE1 -> ARABIC QUESTION MARK
    'S'         # 0xE2 -> LATIN CAPITAL LETTER S
    'T'         # 0xE3 -> LATIN CAPITAL LETTER T
    'U'         # 0xE4 -> LATIN CAPITAL LETTER U
    'V'         # 0xE5 -> LATIN CAPITAL LETTER V
    'W'         # 0xE6 -> LATIN CAPITAL LETTER W
    'X'         # 0xE7 -> LATIN CAPITAL LETTER X
    'Y'         # 0xE8 -> LATIN CAPITAL LETTER Y
    'Z'         # 0xE9 -> LATIN CAPITAL LETTER Z
    '\u0640'    # 0xEA -> ARABIC TATWEEL
    '\u06f0'    # 0xEB -> EXTENDED ARABIC-INDIC DIGIT ZERO
    '\u06f1'    # 0xEC -> EXTENDED ARABIC-INDIC DIGIT ONE
    '\u06f2'    # 0xED -> EXTENDED ARABIC-INDIC DIGIT TWO
    '\u06f3'    # 0xEE -> EXTENDED ARABIC-INDIC DIGIT THREE
    '\u06f4'    # 0xEF -> EXTENDED ARABIC-INDIC DIGIT FOUR
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
    '\u06f5'    # 0xFA -> EXTENDED ARABIC-INDIC DIGIT FIVE
    '\u06f6'    # 0xFB -> EXTENDED ARABIC-INDIC DIGIT SIX
    '\u06f7'    # 0xFC -> EXTENDED ARABIC-INDIC DIGIT SEVEN
    '\u06f8'    # 0xFD -> EXTENDED ARABIC-INDIC DIGIT EIGHT
    '\u06f9'    # 0xFE -> EXTENDED ARABIC-INDIC DIGIT NINE
    '\x9f'      # 0xFF -> APPLICATION PROGRAM COMMAND
)


# Encoding table
encoding_table = codecs.charmap_build(decoding_table)
