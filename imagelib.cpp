//---------------------------------------------------------------------------
#include <vcl.h>
#include <Graphics.hpp>
#include <SysUtils.hpp>
#include <Classes.hpp>
#include <tchar.h>
#include <assert.h>
#include "ColorQuantizationLibrary.h"
#include "PaletteLibrary.h"


#pragma hdrstop

#include "imagelib.h"

//---------------------------------------------------------------------------

#pragma package(smart_init)


__declspec(dllexport) void __stdcall OptimizeBitmap(Graphics::TBitmap* Bitmap)
{
    Graphics::TBitmap* TempBuffer = NULL;
    HPALETTE PaletteHandle = NULL;

    try {
        // Create temporary buffer to store the original bitmap
        TempBuffer = new Graphics::TBitmap();
        TempBuffer->Width = Bitmap->Width;
        TempBuffer->Height = Bitmap->Height;

        // Copy the bitmap to the temporary buffer
        TempBuffer->Canvas->Draw(0, 0, Bitmap);

        // Check if the bitmap pixel format is 24-bit (pf24bit)
        if (Bitmap->PixelFormat == pf24bit) {
            // Release the palette of the bitmap (if any)
            Bitmap->ReleasePalette();

            // Create optimized palette (using 6 color bits for this example)
            PaletteHandle = CreateOptimizedPaletteForSingleBitmap(Bitmap, 6);

            // Change the pixel format to 8-bit
            Bitmap->PixelFormat = pf8bit;

            // Assign the new palette to the bitmap
            Bitmap->Palette = CopyPalette(PaletteHandle);
        }

        // Copy the contents back to the original bitmap
        Bitmap->Canvas->Draw(0, 0, TempBuffer);

    } __finally {
        // Free the temporary buffer
        if (TempBuffer != NULL) {
            delete TempBuffer;
        }
    }
}
