//---------------------------------------------------------------------------
#include <vcl.h>
#include <assert.h>
#include "ColorQuantizationLibrary.h"

#pragma hdrstop

#include "PaletteLibrary.h"

//---------------------------------------------------------------------------

#pragma package(smart_init)

/*
const COLORREF clMoneyGreen = TColor(0xC0DCC0);   // Color   "8"  RGB:  192 220 192
const COLORREF clSkyBlue    = TColor(0xF0CAA6);   // Color   "9"  RGB:  166 202 240
const COLORREF clCream      = TColor(0xF0FBFF);   // Color "246"  RGB:  255 251 240
*/
const COLORREF clMediumGray = TColor(0xA4A0A0);   // Color "247"  RGB:  160 160 164

const COLORREF NonDitherColors[20] = {
    clBlack, clMaroon, clGreen, clOlive, clNavy, clPurple, clTeal, clSilver,
    clMoneyGreen, clSkyBlue, clCream, clMediumGray,
    clGray, clRed, clLime, clYellow, clBlue, clFuchsia, clAqua, clWhite
};

typedef TPaletteEntry TPaletteEntries[256];
typedef TRGBQuad TRGBQuadArray[256];

typedef void (__fastcall *TGetBitmapCallback)(int Counter, bool &OK, Graphics::TBitmap *Bitmap);

// Global variable
int PaletteColorCount;

const DWORD PaletteVersion = 0x0300;  // The palette version (same as PaletteVersion in Delphi)


HPALETTE CreateOptimizedPaletteForSingleBitmap(Graphics::TBitmap *Bitmap, int ColorBits) {
    TColorQuantizer *ColorQuantizer;
    HDC ScreenDeviceContext;
    int i;
    TMaxLogPalette LogicalPalette;
    TRGBQuadArray RGBQuadArray;

    LogicalPalette.palVersion = PaletteVersion;
    LogicalPalette.palNumEntries = 256;

    // Get the system palette entries
    ScreenDeviceContext = GetDC(0);
    try {
        GetSystemPaletteEntries(ScreenDeviceContext, 0, 256, &LogicalPalette.palPalEntry[0]);
    } __finally {
        ReleaseDC(0, ScreenDeviceContext);
    }

    // Create the color quantizer
    ColorQuantizer = new TColorQuantizer(236, ColorBits);
    try {
        // Process the image and get the color table
        if (ColorQuantizer->ProcessImage(Bitmap->Handle)) {
            ColorQuantizer->GetColorTable(RGBQuadArray);

            // Populate the logical palette with the quantized colors
            for (i = 0; i < 256 - 20; i++) {
                LogicalPalette.palPalEntry[10 + i].peRed = RGBQuadArray[i].rgbRed;
                LogicalPalette.palPalEntry[10 + i].peGreen = RGBQuadArray[i].rgbGreen;
                LogicalPalette.palPalEntry[10 + i].peBlue = RGBQuadArray[i].rgbBlue;
                LogicalPalette.palPalEntry[10 + i].peFlags = RGBQuadArray[i].rgbReserved;
            }

            // Create and return the palette
            return CreatePalette((LOGPALETTE*)&LogicalPalette);
        }
    } __finally {
        // Free resources
        delete ColorQuantizer;
    }

    return NULL;
}
