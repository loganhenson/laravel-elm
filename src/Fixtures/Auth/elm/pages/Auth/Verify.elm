module Auth.Verify exposing (..)

import Auth.Layout exposing (authButtonClasses, authContainer)
import Html exposing (Html, button, div, form, text)
import Html.Attributes exposing (class, type_)
import Html.Events exposing (onSubmit)
import Json.Decode exposing (Decoder, Error, Value, bool, decodeValue, dict, list, nullable, string, succeed)
import Json.Decode.Pipeline exposing (required)
import Json.Encode
import LaravelElm exposing (Errors, Page, page)
import Routes exposing (post)


type alias Props =
    { errors : Errors
    , status : Maybe Bool
    }


type alias State =
    {}


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | Submit


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "errors" (dict (list string))
        |> required "status" (nullable bool)


stateFromProps : Props -> State
stateFromProps props =
    {}


main : Page Model Msg
main =
    page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , update = update
        , view = view
        , newPropsMsg = NewProps
        }


update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            ( { props = Result.withDefault props (decodeValue decodeProps newProps)
              , state = state
              }
            , Cmd.none
            )

        Submit ->
            ( { props = props, state = state }
            , post <|
                Json.Encode.object
                    [ ( "url", Json.Encode.string Routes.verificationResend )
                    ]
            )


view : Model -> Html Msg
view { props, state } =
    authContainer "Verify Your Email Address"
        [ form [ onSubmit <| Submit ]
            [ div [ class "flex flex-wrap" ]
                [ text "Please check your email for a verification link."
                , button [ class authButtonClasses, class "mt-4", type_ "submit" ]
                    [ text "Click here to resend the email" ]
                , div [ class "text-indigo-700 mt-4" ]
                    [ case props.status of
                        Just status ->
                            case status of
                                True ->
                                    text "A fresh verification link has been sent to your email address."

                                False ->
                                    text "Something went wrong."

                        Nothing ->
                            text ""
                    ]
                ]
            ]
        ]
