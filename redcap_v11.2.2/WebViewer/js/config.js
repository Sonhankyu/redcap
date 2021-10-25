window.config = {
  user: {},
  product: {
    version: "v 1.33",
    item: "",
    model: "",
    companyIdentificationNumber: "",
    companyAddress: "",
    manufacturingPermitNumber: "",
    serialNumber: "",
    website: "",
  },
  thumbnail: {},
  control: {
    dcmViewScrollLoop: true,
    AllocateAllFramesOnLayout: true,
  },
  documentation: {
    rootPageURL: "documentation/documentation.html",
    shortkeyPageURL: "documentation/shortcut.html",
  },
  tagViewer: {
    rootPageURL: "dicom-data-table.html",
  },
  dicomTag: {
    usePixelSpacingApproximateValue: true,
  },
  lib: {
    dicomLoader: {
      name: "IAID-DicomParser",
      // eslint-disable-next-line no-undef
      entry: IAidDicomLib,
      isUsing: true,
    },
  },
  imageOverlay: {
    presetOpacity: [
      {
        funcKey: "SELECT_OPACITY_0%",
        name: "Opacity 0%",
        value: 0,
      },
      {
        funcKey: "SELECT_OPACITY_20%",
        name: "Opacity 20%",
        value: 0.2,
      },
      {
        funcKey: "SELECT_OPACITY_40%",
        name: "Opacity 40%",
        value: 0.4,
      },
      {
        funcKey: "SELECT_OPACITY_60%",
        name: "Opacity 60%",
        value: 0.6,
      },
      {
        funcKey: "SELECT_OPACITY_80%",
        name: "Opacity 80%",
        value: 0.8,
      },
      {
        funcKey: "SELECT_OPACITY_100%",
        name: "Opacity 100%",
        value: 1.0,
      },
    ],
  },
  presetWindow: {
    CT: [
      {
        funcKey: "SELECT_CONTRAST_CT_LIVER",
        name: "CT Liver",
        type: "Window",
        hotkey: "1",
        value: {
          windowLevel: 100,
          windowWidth: 200,
        },
      },
      {
        funcKey: "SELECT_CONTRAST_CT_ABDOMEN",
        name: "CT Abdomen",
        type: "Window",
        hotkey: "2",
        value: {
          windowLevel: 60,
          windowWidth: 400,
        },
      },
      {
        funcKey: "SELECT_CONTRAST_CT_ANGIO",
        name: "CT Angio",
        type: "Window",
        hotkey: "3",
        value: {
          windowLevel: 300,
          windowWidth: 600,
        },
      },
      {
        funcKey: "SELECT_CONTRAST_CT_BONE",
        name: "CT Bone",
        type: "Window",
        hotkey: "4",
        value: {
          windowLevel: 300,
          windowWidth: 1500,
        },
      },
      {
        funcKey: "SELECT_CONTRAST_CT_BRAIN",
        name: "CT Brain",
        type: "Window",
        hotkey: "5",
        value: {
          windowLevel: 40,
          windowWidth: 80,
        },
      },
      {
        funcKey: "SELECT_CONTRAST_CT_CHEST",
        name: "CT Chest",
        type: "Window",
        hotkey: "6",
        value: {
          windowLevel: 40,
          windowWidth: 400,
        },
      },
      {
        funcKey: "SELECT_CONTRAST_CT_LUNGS",
        name: "CT Lungs",
        type: "Window",
        hotkey: "7",
        value: {
          windowLevel: -400,
          windowWidth: 1500,
        },
      },
    ],
    PT: [
      {
        funcKey: "SELECT_0.5%",
        name: "0.5%",
        type: "DisplayedMaximumValue",
        hotkey: "1",
        value: 0.5,
      },
      {
        funcKey: "SELECT_1%",
        name: "1%",
        type: "DisplayedMaximumValue",
        hotkey: "2",
        value: 1,
      },
      {
        funcKey: "SELECT_2.5%",
        name: "2.5%",
        type: "DisplayedMaximumValue",
        hotkey: "3",
        value: 2.5,
      },
      {
        funcKey: "SELECT_5%",
        name: "5%",
        type: "DisplayedMaximumValue",
        hotkey: "4",
        value: 5,
      },
      {
        funcKey: "SELECT_10%",
        name: "10%",
        type: "DisplayedMaximumValue",
        hotkey: "5",
        value: 10,
      },
      {
        funcKey: "SELECT_25%",
        name: "25%",
        type: "DisplayedMaximumValue",
        hotkey: "6",
        value: 25,
      },
      {
        funcKey: "SELECT_50%",
        name: "50%",
        type: "DisplayedMaximumValue",
        hotkey: "7",
        value: 50,
      },
      {
        funcKey: "SELECT_100%",
        name: "100%",
        type: "DisplayedMaximumValue",
        hotkey: "8",
        value: 100,
      },
    ],
    MR: [
      {
        funcKey: "SELECT_[20/40]",
        name: "[20/40]",
        type: "Window",
        hotkey: "1",
        value: {
          windowLevel: 20,
          windowWidth: 40,
        },
      },
      {
        funcKey: "SELECT_[40/80]",
        name: "[40/80]",
        type: "Window",
        hotkey: "2",
        value: {
          windowLevel: 40,
          windowWidth: 80,
        },
      },
      {
        funcKey: "SELECT_[80/160]",
        name: "[80/160]",
        type: "Window",
        hotkey: "3",
        value: {
          windowLevel: 80,
          windowWidth: 160,
        },
      },
      {
        funcKey: "SELECT_[160/320]",
        name: "[160/320]",
        type: "Window",
        hotkey: "4",
        value: {
          windowLevel: 160,
          windowWidth: 320,
        },
      },
      {
        funcKey: "SELECT_[320/640]",
        name: "[320/640]",
        type: "Window",
        hotkey: "5",
        value: {
          windowLevel: 320,
          windowWidth: 640,
        },
      },
      {
        funcKey: "SELECT_[640/1280]",
        name: "[640/1280]",
        type: "Window",
        hotkey: "6",
        value: {
          windowLevel: 320,
          windowWidth: 640,
        },
      },
      {
        funcKey: "SELECT_[1280/2560]",
        name: "[1280/2560]",
        type: "Window",
        hotkey: "7",
        value: {
          windowLevel: 1280,
          windowWidth: 2560,
        },
      },
      {
        funcKey: "SELECT_[2560/5120]",
        name: "[2560/5120]",
        type: "Window",
        hotkey: "8",
        value: {
          windowLevel: 2560,
          windowWidth: 5120,
        },
      },
    ],
  },
};
