""" 
Python Character Mapping Codec cp420 generated from 'temp/cp420.txt' with gencodec.py.
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
        name='cp420',
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
    '\u0651'    # 0x42 -> ARABIC SHADDA
    '\ufe7d'    # 0x43 -> ARABIC SHADDA MEDIAL FORM
    '\u0640'    # 0x44 -> ARABIC TATWEEL
    '\u200b'    # 0x45 -> ZERO WIDTH SPACE
    '\u0621'    # 0x46 -> ARABIC LETTER HAMZA
    '\u0622'    # 0x47 -> ARABIC LETTER ALEF WITH MADDA ABOVE
    '\ufe82'    # 0x48 -> ARABIC LETTER ALEF WITH MADDA ABOVE FINAL FORM
    '\u0623'    # 0x49 -> ARABIC LETTER ALEF WITH HAMZA ABOVE
    '\xa2'      # 0x4A -> CENT SIGN
    '.'         # 0x4B -> FULL STOP
    '<'         # 0x4C -> LESS-THAN SIGN
    '('         # 0x4D -> LEFT PARENTHESIS
    '+'         # 0x4E -> PLUS SIGN
    '|'         # 0x4F -> VERTICAL LINE
    '&'         # 0x50 -> AMPERSAND
    '\ufe84'    # 0x51 -> ARABIC LETTER ALEF WITH HAMZA ABOVE FINAL FORM
    '\u0624'    # 0x52 -> ARABIC LETTER WAW WITH HAMZA ABOVE
    '\ufffd'    # 0x53 -> REPLACEMENT CHARACTER
    '\ufffd'    # 0x54 -> REPLACEMENT CHARACTER
    '\u0626'    # 0x55 -> ARABIC LETTER YEH WITH HAMZA ABOVE
    '\u0627'    # 0x56 -> ARABIC LETTER ALEF
    '\ufe8e'    # 0x57 -> ARABIC LETTER ALEF FINAL FORM
    '\u0628'    # 0x58 -> ARABIC LETTER BEH
    '\ufe91'    # 0x59 -> ARABIC LETTER BEH INITIAL FORM
    '!'         # 0x5A -> EXCLAMATION MARK
    '$'         # 0x5B -> DOLLAR SIGN
    '*'         # 0x5C -> ASTERISK
    ')'         # 0x5D -> RIGHT PARENTHESIS
    ';'         # 0x5E -> SEMICOLON
    '\xac'      # 0x5F -> NOT SIGN
    '-'         # 0x60 -> HYPHEN-MINUS
    '/'         # 0x61 -> SOLIDUS
    '\u0629'    # 0x62 -> ARABIC LETTER TEH MARBUTA
    '\u062a'    # 0x63 -> ARABIC LETTER TEH
    '\ufe97'    # 0x64 -> ARABIC LETTER TEH INITIAL FORM
    '\u062b'    # 0x65 -> ARABIC LETTER THEH
    '\ufe9b'    # 0x66 -> ARABIC LETTER THEH INITIAL FORM
    '\u062c'    # 0x67 -> ARABIC LETTER JEEM
    '\ufe9f'    # 0x68 -> ARABIC LETTER JEEM INITIAL FORM
    '\u062d'    # 0x69 -> ARABIC LETTER HAH
    '\xa6'      # 0x6A -> BROKEN BAR
    ','         # 0x6B -> COMMA
    '%'         # 0x6C -> PERCENT SIGN
    '_'         # 0x6D -> LOW LINE
    '>'         # 0x6E -> GREATER-THAN SIGN
    '?'         # 0x6F -> QUESTION MARK
    '\ufea3'    # 0x70 -> ARABIC LETTER HAH INITIAL FORM
    '\u062e'    # 0x71 -> ARABIC LETTER KHAH
    '\ufea7'    # 0x72 -> ARABIC LETTER KHAH INITIAL FORM
    '\u062f'    # 0x73 -> ARABIC LETTER DAL
    '\u0630'    # 0x74 -> ARABIC LETTER THAL
    '\u0631'    # 0x75 -> ARABIC LETTER REH
    '\u0632'    # 0x76 -> ARABIC LETTER ZAIN
    '\u0633'    # 0x77 -> ARABIC LETTER SEEN
    '\ufeb3'    # 0x78 -> ARABIC LETTER SEEN INITIAL FORM
    '\u060c'    # 0x79 -> ARABIC COMMA
    ':'         # 0x7A -> COLON
    '#'         # 0x7B -> NUMBER SIGN
    '@'         # 0x7C -> COMMERCIAL AT
    "'"         # 0x7D -> APOSTROPHE
    '='         # 0x7E -> EQUALS SIGN
    '"'         # 0x7F -> QUOTATION MARK
    '\u0634'    # 0x80 -> ARABIC LETTER SHEEN
    'a'         # 0x81 -> LATIN SMALL LETTER A
    'b'         # 0x82 -> LATIN SMALL LETTER B
    'c'         # 0x83 -> LATIN SMALL LETTER C
    'd'         # 0x84 -> LATIN SMALL LETTER D
    'e'         # 0x85 -> LATIN SMALL LETTER E
    'f'         # 0x86 -> LATIN SMALL LETTER F
    'g'         # 0x87 -> LATIN SMALL LETTER G
    'h'         # 0x88 -> LATIN SMALL LETTER H
    'i'         # 0x89 -> LATIN SMALL LETTER I
    '\ufeb7'    # 0x8A -> ARABIC LETTER SHEEN INITIAL FORM
    '\u0635'    # 0x8B -> ARABIC LETTER SAD
    '\ufebb'    # 0x8C -> ARABIC LETTER SAD INITIAL FORM
    '\u0636'    # 0x8D -> ARABIC LETTER DAD
    '\ufebf'    # 0x8E -> ARABIC LETTER DAD INITIAL FORM
    '\u0637'    # 0x8F -> ARABIC LETTER TAH
    '\u0638'    # 0x90 -> ARABIC LETTER ZAH
    'j'         # 0x91 -> LATIN SMALL LETTER J
    'k'         # 0x92 -> LATIN SMALL LETTER K
    'l'         # 0x93 -> LATIN SMALL LETTER L
    'm'         # 0x94 -> LATIN SMALL LETTER M
    'n'         # 0x95 -> LATIN SMALL LETTER N
    'o'         # 0x96 -> LATIN SMALL LETTER O
    'p'         # 0x97 -> LATIN SMALL LETTER P
    'q'         # 0x98 -> LATIN SMALL LETTER Q
    'r'         # 0x99 -> LATIN SMALL LETTER R
    '\u0639'    # 0x9A -> ARABIC LETTER AIN
    '\ufeca'    # 0x9B -> ARABIC LETTER AIN FINAL FORM
    '\ufecb'    # 0x9C -> ARABIC LETTER AIN INITIAL FORM
    '\ufecc'    # 0x9D -> ARABIC LETTER AIN MEDIAL FORM
    '\u063a'    # 0x9E -> ARABIC LETTER GHAIN
    '\ufece'    # 0x9F -> ARABIC LETTER GHAIN FINAL FORM
    '\ufecf'    # 0xA0 -> ARABIC LETTER GHAIN INITIAL FORM
    '\xf7'      # 0xA1 -> DIVISION SIGN
    's'         # 0xA2 -> LATIN SMALL LETTER S
    't'         # 0xA3 -> LATIN SMALL LETTER T
    'u'         # 0xA4 -> LATIN SMALL LETTER U
    'v'         # 0xA5 -> LATIN SMALL LETTER V
    'w'         # 0xA6 -> LATIN SMALL LETTER W
    'x'         # 0xA7 -> LATIN SMALL LETTER X
    'y'         # 0xA8 -> LATIN SMALL LETTER Y
    'z'         # 0xA9 -> LATIN SMALL LETTER Z
    '\ufed0'    # 0xAA -> ARABIC LETTER GHAIN MEDIAL FORM
    '\u0641'    # 0xAB -> ARABIC LETTER FEH
    '\ufed3'    # 0xAC -> ARABIC LETTER FEH INITIAL FORM
    '\u0642'    # 0xAD -> ARABIC LETTER QAF
    '\ufed7'    # 0xAE -> ARABIC LETTER QAF INITIAL FORM
    '\u0643'    # 0xAF -> ARABIC LETTER KAF
    '\ufedb'    # 0xB0 -> ARABIC LETTER KAF INITIAL FORM
    '\u0644'    # 0xB1 -> ARABIC LETTER LAM
    '\ufef5'    # 0xB2 -> ARABIC LIGATURE LAM WITH ALEF WITH MADDA ABOVE ISOLATED FORM
    '\ufef6'    # 0xB3 -> ARABIC LIGATURE LAM WITH ALEF WITH MADDA ABOVE FINAL FORM
    '\ufef7'    # 0xB4 -> ARABIC LIGATURE LAM WITH ALEF WITH HAMZA ABOVE ISOLATED FORM
    '\ufef8'    # 0xB5 -> ARABIC LIGATURE LAM WITH ALEF WITH HAMZA ABOVE FINAL FORM
    '\ufffd'    # 0xB6 -> REPLACEMENT CHARACTER
    '\ufffd'    # 0xB7 -> REPLACEMENT CHARACTER
    '\ufefb'    # 0xB8 -> ARABIC LIGATURE LAM WITH ALEF ISOLATED FORM
    '\ufefc'    # 0xB9 -> ARABIC LIGATURE LAM WITH ALEF FINAL FORM
    '\ufedf'    # 0xBA -> ARABIC LETTER LAM INITIAL FORM
    '\u0645'    # 0xBB -> ARABIC LETTER MEEM
    '\ufee3'    # 0xBC -> ARABIC LETTER MEEM INITIAL FORM
    '\u0646'    # 0xBD -> ARABIC LETTER NOON
    '\ufee7'    # 0xBE -> ARABIC LETTER NOON INITIAL FORM
    '\u0647'    # 0xBF -> ARABIC LETTER HEH
    '\u061b'    # 0xC0 -> ARABIC SEMICOLON
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
    '\ufeeb'    # 0xCB -> ARABIC LETTER HEH INITIAL FORM
    '\ufffd'    # 0xCC -> REPLACEMENT CHARACTER
    '\ufeec'    # 0xCD -> ARABIC LETTER HEH MEDIAL FORM
    '\ufffd'    # 0xCE -> REPLACEMENT CHARACTER
    '\u0648'    # 0xCF -> ARABIC LETTER WAW
    '\u061f'    # 0xD0 -> ARABIC QUESTION MARK
    'J'         # 0xD1 -> LATIN CAPITAL LETTER J
    'K'         # 0xD2 -> LATIN CAPITAL LETTER K
    'L'         # 0xD3 -> LATIN CAPITAL LETTER L
    'M'         # 0xD4 -> LATIN CAPITAL LETTER M
    'N'         # 0xD5 -> LATIN CAPITAL LETTER N
    'O'         # 0xD6 -> LATIN CAPITAL LETTER O
    'P'         # 0xD7 -> LATIN CAPITAL LETTER P
    'Q'         # 0xD8 -> LATIN CAPITAL LETTER Q
    'R'         # 0xD9 -> LATIN CAPITAL LETTER R
    '\u0649'    # 0xDA -> ARABIC LETTER ALEF MAKSURA
    '\ufef0'    # 0xDB -> ARABIC LETTER ALEF MAKSURA FINAL FORM
    '\u064a'    # 0xDC -> ARABIC LETTER YEH
    '\ufef2'    # 0xDD -> ARABIC LETTER YEH FINAL FORM
    '\ufef3'    # 0xDE -> ARABIC LETTER YEH INITIAL FORM
    '\u0660'    # 0xDF -> ARABIC-INDIC DIGIT ZERO
    '\xd7'      # 0xE0 -> MULTIPLICATION SIGN
    '\ufffd'    # 0xE1 -> REPLACEMENT CHARACTER
    'S'         # 0xE2 -> LATIN CAPITAL LETTER S
    'T'         # 0xE3 -> LATIN CAPITAL LETTER T
    'U'         # 0xE4 -> LATIN CAPITAL LETTER U
    'V'         # 0xE5 -> LATIN CAPITAL LETTER V
    'W'         # 0xE6 -> LATIN CAPITAL LETTER W
    'X'         # 0xE7 -> LATIN CAPITAL LETTER X
    'Y'         # 0xE8 -> LATIN CAPITAL LETTER Y
    'Z'         # 0xE9 -> LATIN CAPITAL LETTER Z
    '\u0661'    # 0xEA -> ARABIC-INDIC DIGIT ONE
    '\u0662'    # 0xEB -> ARABIC-INDIC DIGIT TWO
    '\ufffd'    # 0xEC -> REPLACEMENT CHARACTER
    '\u0663'    # 0xED -> ARABIC-INDIC DIGIT THREE
    '\u0664'    # 0xEE -> ARABIC-INDIC DIGIT FOUR
    '\u0665'    # 0xEF -> ARABIC-INDIC DIGIT FIVE
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
    '\u0666'    # 0xFB -> ARABIC-INDIC DIGIT SIX
    '\u0667'    # 0xFC -> ARABIC-INDIC DIGIT SEVEN
    '\u0668'    # 0xFD -> ARABIC-INDIC DIGIT EIGHT
    '\u0669'    # 0xFE -> ARABIC-INDIC DIGIT NINE
    '\x9f'      # 0xFF -> APPLICATION PROGRAM COMMAND
)


# Encoding table
encoding_table = codecs.charmap_build(decoding_table)
